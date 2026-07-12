(function () {
  'use strict';

  const data = window.fbsaWidgetData || {};

  const translationCatalog = data.translationCatalog || {};
  const wpTranslate = window.wp && window.wp.i18n && typeof window.wp.i18n.__ === 'function'
    ? window.wp.i18n.__
    : null;
  const translationPatternKeys = Object.keys(translationCatalog).filter(function (key) {
    return /%(?:\d+\$)?[sdf]/.test(key);
  }).sort(function (left, right) {
    return right.length - left.length;
  });

  function escapeRegex(value) {
    return String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  }

  function patternToRegex(pattern) {
    let source = '^';
    let lastIndex = 0;
    const placeholder = /%(?:\d+\$)?[sdf]/g;
    let match = placeholder.exec(pattern);
    while (match) {
      source += escapeRegex(pattern.slice(lastIndex, match.index)) + '(.+?)';
      lastIndex = match.index + match[0].length;
      match = placeholder.exec(pattern);
    }
    source += escapeRegex(pattern.slice(lastIndex)) + '$';
    return new RegExp(source);
  }

  function formatTranslatedPattern(template, values) {
    let index = 0;
    return String(template).replace(/%(?:\d+\$)?[sdf]/g, function () {
      const value = index < values.length ? values[index] : '';
      index += 1;
      return value;
    });
  }

  function translateText(value) {
    if (typeof value !== 'string' || value === '') return value;
    const leading = (value.match(/^\s+/) || [''])[0];
    const trailing = (value.match(/\s+$/) || [''])[0];
    const source = value.trim();
    if (!source) return value;

    if (Object.prototype.hasOwnProperty.call(translationCatalog, source)) {
      return leading + translationCatalog[source] + trailing;
    }

    for (let i = 0; i < translationPatternKeys.length; i += 1) {
      const pattern = translationPatternKeys[i];
      const match = source.match(patternToRegex(pattern));
      if (match) {
        return leading + formatTranslatedPattern(translationCatalog[pattern], match.slice(1)) + trailing;
      }
    }

    return leading + (wpTranslate ? wpTranslate(source, 'fb-software-ai') : source) + trailing;
  }

  const pluginRootSelector = [
    '#fbsa-floating-widget',
    '#fbsa-expanded-video-player',
    '.fbsa-settings-page',
    '.fbsa-dashboard-welcome',
    '.fbsa-dashboard-steps',
    '#fbsa-confirm-modal'
  ].join(',');

  function isInsidePluginUi(node) {
    if (!node) return false;
    const element = node.nodeType === Node.ELEMENT_NODE ? node : node.parentElement;
    return !!(element && (element.matches(pluginRootSelector) || element.closest(pluginRootSelector)));
  }

  function translatePluginNode(root) {
    if (!root || !isInsidePluginUi(root)) return;

    if (root.nodeType === Node.TEXT_NODE) {
      root.nodeValue = translateText(root.nodeValue || '');
      return;
    }

    if (root.nodeType !== Node.ELEMENT_NODE) return;
    const element = root;
    ['aria-label', 'title', 'placeholder', 'data-confirm-message', 'data-empty-message'].forEach(function (attribute) {
      if (element.hasAttribute(attribute)) {
        element.setAttribute(attribute, translateText(element.getAttribute(attribute) || ''));
      }
    });
    if (element.matches('input[type="submit"], input[type="button"]')) {
      element.value = translateText(element.value || '');
    }

    const walker = document.createTreeWalker(element, NodeFilter.SHOW_TEXT);
    let textNode = walker.nextNode();
    while (textNode) {
      if (!textNode.parentElement || !textNode.parentElement.closest('script,style,code,pre')) {
        textNode.nodeValue = translateText(textNode.nodeValue || '');
      }
      textNode = walker.nextNode();
    }

    element.querySelectorAll('[aria-label], [title], [placeholder], [data-confirm-message], [data-empty-message], input[type="submit"], input[type="button"]').forEach(function (child) {
      ['aria-label', 'title', 'placeholder', 'data-confirm-message', 'data-empty-message'].forEach(function (attribute) {
        if (child.hasAttribute(attribute)) {
          child.setAttribute(attribute, translateText(child.getAttribute(attribute) || ''));
        }
      });
      if (child.matches('input[type="submit"], input[type="button"]')) {
        child.value = translateText(child.value || '');
      }
    });
  }

  function initializePluginTranslations() {
    document.querySelectorAll(pluginRootSelector).forEach(translatePluginNode);
    if (!document.body || typeof MutationObserver !== 'function') return;

    const observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        mutation.addedNodes.forEach(function (node) {
          if (isInsidePluginUi(node)) {
            translatePluginNode(node);
          } else if (node.nodeType === Node.ELEMENT_NODE) {
            node.querySelectorAll(pluginRootSelector).forEach(translatePluginNode);
          }
        });
      });
    });
    observer.observe(document.body, { childList: true, subtree: true });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePluginTranslations, { once: true });
  } else {
    initializePluginTranslations();
  }
  const workflow = data.commands || { categories: [] };
  const workflowLoadError = data.workflowError || '';
  const widgetThemeStorageKey = 'fbsaWidgetTheme';
  const widgetPositionStorageKey = 'fbsaWidgetPosition';
  const widgetCollapsedStorageKey = 'fbsaWidgetCollapsed';
  const state = {
    selectedCategory: '',
    selectedCommand: null,
    selectedSubcategory: '',
    sectionCompletionMessage: '',
    completedIds: new Set(),
    installedPluginSlugs: new Set(data.installedPlugins || []),
    installedThemeSlugs: new Set(data.installedThemes || []),
    activePluginSlugs: new Set(data.activePlugins || []),
    isBusy: false,
    currentVideoUrl: '',
    currentVideoEmbed: '',
    currentVideoTitle: translateText('Guide Video'),
    playerVideoUrl: '',
    playerVideoEmbed: '',
    playerVideoTitle: translateText('Guide Video'),
    playerVideoStartTime: 0,
    playerVideoCurrentTime: 0,
    expandedVideoOpen: false,
    expandedVideoPosition: null,
    expandedVideoSize: null,
    expandedVideoMini: false,
    persistentVideoPopup: null,
    youtubeApiPromise: null,
    youtubePlayer: null,
    youtubeTimeTimer: null,
    floatingWidgetPosition: null,
  };

  function ready(callback) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', callback);
    } else {
      callback();
    }
  }

  function $(selector, root = document) {
    return root.querySelector(selector);
  }

  function getSystemWidgetTheme() {
    try {
      return window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
    } catch (error) {
      return 'dark';
    }
  }

  function getStoredWidgetTheme() {
    try {
      const stored = window.localStorage ? window.localStorage.getItem(widgetThemeStorageKey) : '';
      return stored === 'light' || stored === 'dark' ? stored : '';
    } catch (error) {
      return '';
    }
  }

  function persistWidgetTheme(theme) {
    try {
      if (!window.localStorage) return;
      window.localStorage.setItem(widgetThemeStorageKey, theme);
    } catch (error) {
      // Ignore storage errors and keep the current in-memory theme.
    }
  }

  function getStoredWidgetCollapsedState() {
    try {
      const stored = window.localStorage ? window.localStorage.getItem(widgetCollapsedStorageKey) : '';
      if (stored === 'true') return true;
      if (stored === 'false') return false;
      return null;
    } catch (error) {
      return null;
    }
  }

  function persistWidgetCollapsedState(isCollapsed) {
    try {
      if (!window.localStorage) return;
      window.localStorage.setItem(widgetCollapsedStorageKey, isCollapsed ? 'true' : 'false');
    } catch (error) {
      // Ignore storage errors and keep the current in-memory collapsed state.
    }
  }

  function clampNumber(value, min, max) {
    return Math.min(Math.max(value, min), max);
  }

  function getStoredWidgetPosition() {
    try {
      const stored = window.localStorage ? window.localStorage.getItem(widgetPositionStorageKey) : '';
      if (!stored) return null;
      const parsed = JSON.parse(stored);
      if (!parsed || typeof parsed.top !== 'number') {
        return null;
      }
      if (parsed.anchorX === 'right' && typeof parsed.x === 'number') {
        return parsed;
      }
      if (typeof parsed.left !== 'number' && !(parsed.anchorX === 'left' && typeof parsed.x === 'number')) {
        return null;
      }
      return parsed;
    } catch (error) {
      return null;
    }
  }

  function persistWidgetPosition(position) {
    try {
      if (!window.localStorage) return;
      if (!position) {
        window.localStorage.removeItem(widgetPositionStorageKey);
        return;
      }
      window.localStorage.setItem(widgetPositionStorageKey, JSON.stringify(position));
    } catch (error) {
      // Ignore storage errors and keep the current in-memory position.
    }
  }

  function clearFloatingWidgetInlinePosition(widget) {
    if (!widget) return;
    widget.style.removeProperty('left');
    widget.style.removeProperty('top');
    widget.style.setProperty('right', 'var(--fbsa-edge-offset)', 'important');
    widget.style.setProperty('bottom', 'var(--fbsa-edge-offset)', 'important');
    state.floatingWidgetPosition = null;
  }

  function clampFloatingWidgetPosition(left, top, widget) {
    const rect = widget.getBoundingClientRect();
    const computed = window.getComputedStyle(widget);
    const edgeOffset = parseFloat(computed.getPropertyValue('--fbsa-edge-offset')) || 22;
    const horizontalEdgeOffset = 0;
    const maxLeft = Math.max(horizontalEdgeOffset, window.innerWidth - rect.width - horizontalEdgeOffset);
    const maxTop = Math.max(edgeOffset, window.innerHeight - rect.height - edgeOffset);

    return {
      left: Math.round(clampNumber(left, horizontalEdgeOffset, maxLeft)),
      top: Math.round(clampNumber(top, edgeOffset, maxTop)),
    };
  }

  function applyFloatingWidgetPosition(widget, position, persist) {
    if (!widget || !position) return;
    const rect = widget.getBoundingClientRect();
    const horizontalEdgeOffset = 0;
    const resolvedLeft = position.anchorX === 'right'
      ? window.innerWidth - rect.width - clampNumber(position.x, horizontalEdgeOffset, Math.max(horizontalEdgeOffset, window.innerWidth - rect.width))
      : (typeof position.left === 'number' ? position.left : position.x);
    const next = clampFloatingWidgetPosition(resolvedLeft, position.top, widget);
    const nextRightGap = Math.max(0, Math.round(window.innerWidth - (next.left + rect.width)));
    const shouldAnchorRight = position.anchorX === 'right' || nextRightGap <= 2;

    if (shouldAnchorRight) {
      widget.style.setProperty('left', 'auto', 'important');
      widget.style.setProperty('right', nextRightGap + 'px', 'important');
    } else {
      widget.style.setProperty('left', next.left + 'px', 'important');
      widget.style.setProperty('right', 'auto', 'important');
    }
    widget.style.setProperty('top', next.top + 'px', 'important');
    widget.style.setProperty('bottom', 'auto', 'important');
    state.floatingWidgetPosition = shouldAnchorRight
      ? { anchorX: 'right', x: nextRightGap, top: next.top }
      : { anchorX: 'left', x: next.left, left: next.left, top: next.top };

    if (persist) {
      persistWidgetPosition(state.floatingWidgetPosition);
    }
  }

  function syncFloatingWidgetPosition(widget) {
    if (!widget) return;
    const storedPosition = state.floatingWidgetPosition || getStoredWidgetPosition();
    if (storedPosition) {
      applyFloatingWidgetPosition(widget, storedPosition, false);
      return;
    }
    clearFloatingWidgetInlinePosition(widget);
  }

  function applyWidgetTheme(widget, theme) {
    if (!widget) return;
    const nextTheme = theme === 'light' ? 'light' : 'dark';
    const toggle = $('[data-fbsa-theme-toggle]', widget);
    const label = $('.fbsa-widget__theme-toggle-label', widget);
    const player = document.getElementById('fbsa-expanded-video-player');
    widget.setAttribute('data-fbsa-theme', nextTheme);

    if (player) {
      player.setAttribute('data-fbsa-theme', nextTheme);
    }

    if (label) {
      label.textContent = translateText(nextTheme === 'light' ? 'Light' : 'Dark');
    }

    if (toggle) {
      const switchToTheme = nextTheme === 'light' ? 'dark' : 'light';
      toggle.setAttribute('aria-label', translateText('Switch to ' + switchToTheme + ' theme'));
      toggle.setAttribute('title', translateText('Switch to ' + switchToTheme + ' theme'));
    }
  }

  function initWidgetTheme(widget) {
    if (!widget) return;
    const toggle = $('[data-fbsa-theme-toggle]', widget);
    const storedTheme = getStoredWidgetTheme();
    const initialTheme = storedTheme || getSystemWidgetTheme();

    applyWidgetTheme(widget, initialTheme);

    if (toggle) {
      toggle.addEventListener('click', function () {
        const currentTheme = widget.getAttribute('data-fbsa-theme') === 'light' ? 'light' : 'dark';
        const nextTheme = currentTheme === 'light' ? 'dark' : 'light';
        applyWidgetTheme(widget, nextTheme);
        persistWidgetTheme(nextTheme);
      });
    }

  }

  function applyFloatingWidgetViewportSizing() {
    const widget = $('#fbsa-floating-widget');
    if (!widget) return;

    const panel = $('.fbsa-widget__panel', widget);
    const body = $('.fbsa-widget__body', widget);
    const header = $('.fbsa-widget__header', widget);
    const rail = $('.fbsa-widget__shortcut-rail', widget);
    const isNarrowViewport = window.innerWidth <= 782;
    const isCompactHeight = window.innerHeight <= 760;
    const useBottomRail = isNarrowViewport || isCompactHeight || window.innerWidth <= 1180;
    const viewportMargin = isNarrowViewport ? 16 : 22;
    const widthPadding = useBottomRail ? 32 : (isNarrowViewport ? 72 : 96);
    const maxWidgetWidth = Math.max(280, window.innerWidth - widthPadding);
    const preferredWidth = Math.min(isNarrowViewport ? maxWidgetWidth : 436, maxWidgetWidth);
    const maxWidgetHeight = Math.max(320, window.innerHeight - viewportMargin * 2);

    widget.classList.toggle('fbsa-widget--mobile', window.innerWidth <= 640);
    widget.classList.toggle('fbsa-widget--compact-height', isCompactHeight);
    widget.classList.toggle('fbsa-widget--rail-bottom', useBottomRail);
    widget.style.setProperty('--fbsa-edge-offset', viewportMargin + 'px');
    widget.style.setProperty('--fbsa-widget-panel-width', preferredWidth + 'px');
    widget.style.setProperty('--fbsa-widget-max-height', maxWidgetHeight + 'px');
    widget.style.width = preferredWidth + 'px';
    widget.style.maxWidth = maxWidgetWidth + 'px';
    widget.style.maxHeight = maxWidgetHeight + 'px';

    if (panel) {
      panel.style.maxHeight = maxWidgetHeight + 'px';
    }

    if (body) {
      const headerHeight = header ? header.offsetHeight : 74;
      const bodyMaxHeight = Math.max(180, maxWidgetHeight - headerHeight - (useBottomRail ? 16 : 0));
      body.style.maxHeight = bodyMaxHeight + 'px';
      body.style.overflowY = 'auto';
      body.style.overscrollBehavior = 'contain';
    }

    if (rail) {
      rail.style.maxHeight = useBottomRail ? '' : maxWidgetHeight + 'px';
    }

    syncFloatingWidgetPosition(widget);
  }

  function initFloatingWidgetDrag(widget) {
    let dragState = null;

    document.addEventListener('pointerdown', function (event) {
      const handle = event.target.closest('[data-fbsa-widget-drag-handle]');
      if (!handle || !widget.contains(handle)) return;
      if (event.target.closest('button, a, input, select, textarea, option, label')) return;

      const rect = widget.getBoundingClientRect();
      dragState = {
        handle,
        offsetX: event.clientX - rect.left,
        offsetY: event.clientY - rect.top,
      };

      widget.classList.add('fbsa-widget--dragging');
      handle.setPointerCapture?.(event.pointerId);
      event.preventDefault();
    });

    document.addEventListener('pointermove', function (event) {
      if (!dragState) return;
      applyFloatingWidgetPosition(widget, {
        left: event.clientX - dragState.offsetX,
        top: event.clientY - dragState.offsetY,
      }, false);
      event.preventDefault();
    });

    function finishDrag() {
      if (!dragState) return;
      widget.classList.remove('fbsa-widget--dragging');
      if (state.floatingWidgetPosition) {
        persistWidgetPosition(state.floatingWidgetPosition);
      }
      dragState = null;
    }

    document.addEventListener('pointerup', finishDrag);
    document.addEventListener('pointercancel', finishDrag);
  }

  function buildAdminUrl(path) {
    if (!path) return data.adminUrl || '/wp-admin/';
    if (/^https?:\/\//i.test(path)) return path;
    return String(data.adminUrl || '/wp-admin/') + String(path).replace(/^\//, '');
  }


  function resolveCommandAdminPath(command) {
    if (!command) return 'index.php';
    const requiredPlugin = String(command.requiresPluginSlug || '').trim();
    if (requiredPlugin && !state.activePluginSlugs.has(requiredPlugin)) {
      return command.inactiveAdminPath || 'plugins.php';
    }
    return command.adminPath || 'index.php';
  }

  function findCategory(categoryId) {
    return (workflow.categories || []).find((category) => category.id === categoryId) || null;
  }

  function findSubcategory(category, subcategoryId) {
    if (!category || !subcategoryId || subcategoryId === '__root') return null;
    return (category.subcategories || []).find((item) => item.id === subcategoryId) || null;
  }

  function getRootCommands(category) {
    return category && Array.isArray(category.commands) ? category.commands : [];
  }

  function getActiveCommands(category) {
    if (!category) return [];
    if (Array.isArray(category.subcategories) && category.subcategories.length) {
      if (state.selectedSubcategory === '__root') {
        return getRootCommands(category);
      }
      const subcategory = findSubcategory(category, state.selectedSubcategory);
      return subcategory && Array.isArray(subcategory.commands) ? subcategory.commands : [];
    }
    return getRootCommands(category);
  }

  function findCommand(commandId) {
    for (const category of workflow.categories || []) {
      const rootCommand = (category.commands || []).find((item) => item.id === commandId);
      if (rootCommand) return rootCommand;

      for (const subcategory of category.subcategories || []) {
        const command = (subcategory.commands || []).find((item) => item.id === commandId);
        if (command) return command;
      }
    }
    return null;
  }

  function isCompleted(command) {
    return command && command.hideWhenExists && state.completedIds.has(command.id);
  }

  function isInstalledPluginCommand(command) {
    return command && command.type === 'install_plugin' && command.pluginSlug && state.installedPluginSlugs.has(command.pluginSlug);
  }

  function isInstalledThemeCommand(command) {
    return command && command.type === 'install_theme' && command.themeSlug && state.installedThemeSlugs.has(command.themeSlug);
  }

  function isHiddenCommand(command) {
    if (!command) return false;
    if (isCompleted(command)) return true;
    if (command.hideWhenInstalled && isInstalledPluginCommand(command)) return true;
    if (command.hideWhenInstalled && isInstalledThemeCommand(command)) return true;
    return false;
  }

  function cleanInstallLabel(label, fallback) {
    let name = String(label || fallback || '').trim();
    name = name.replace(/^Install\s+/i, '').trim();
    name = name.replace(/\s+Plugin$/i, '').trim();
    return name || fallback || 'Item';
  }

  function buildSectionCompletionMessage(commands) {
    const list = Array.isArray(commands) ? commands : [];
    if (!list.length) {
      return translateText('This section has no commands yet.');
    }

    const hiddenThemeCommands = list.filter((command) => command && command.type === 'install_theme' && command.themeSlug && state.installedThemeSlugs.has(command.themeSlug));
    if (hiddenThemeCommands.length && hiddenThemeCommands.length === list.length) {
      const names = hiddenThemeCommands.map((command) => cleanInstallLabel(command.label, command.themeSlug));
      return translateText(names.join(', ') + (names.length === 1 ? ' is installed.' : ' are installed.'));
    }

    const hiddenPluginCommands = list.filter((command) => command && command.type === 'install_plugin' && command.pluginSlug && state.installedPluginSlugs.has(command.pluginSlug));
    if (hiddenPluginCommands.length && hiddenPluginCommands.length === list.length) {
      const names = hiddenPluginCommands.map((command) => cleanInstallLabel(command.label, command.pluginSlug));
      return translateText(names.join(', ') + (names.length === 1 ? ' plugin is installed.' : ' plugins are installed.'));
    }

    if (list.every((command) => isHiddenCommand(command))) {
      return translateText('This section is complete.');
    }

    return '';
  }


  function shouldShowAllRequiredPluginsInstalled(category, commands) {
    const list = Array.isArray(commands) ? commands : [];
    return !!(
      category &&
      category.id === 'install_required_plugins' &&
      list.length &&
      list.every((command) => command && command.type === 'install_plugin' && command.pluginSlug && state.installedPluginSlugs.has(command.pluginSlug))
    );
  }

  function getSectionCompletionSelectLabel(category, commands, fallbackMessage) {
    if (shouldShowAllRequiredPluginsInstalled(category, commands)) {
      return translateText('All Required Plugins are installed');
    }
    return fallbackMessage || translateText('This section is complete.');
  }

  function setMessage(message, type = '') {
    const box = $('#fbsa-command-message');
    if (!box) return;
    box.textContent = message || '';
    box.className = 'fbsa-command-message' + (type ? ' fbsa-command-message--' + type : '');
  }

  function updateVideo(command) {
    const box = $('#fbsa-video-box');
    if (!box) return;

    const videoUrl = command && command.videoUrl ? command.videoUrl : '';
    const title = command && command.label ? command.label : translateText('Guide Video');
    const embed = videoUrl ? toYouTubeEmbed(videoUrl) : '';
    const videoLanguage = command && command.videoLanguageLabel ? command.videoLanguageLabel : '';
    const videoBadge = videoLanguage
      ? translateText(videoLanguage + (command && command.videoIsFallback ? ' fallback video' : ' video'))
      : '';

    state.currentVideoUrl = videoUrl;
    state.currentVideoEmbed = embed;
    state.currentVideoTitle = title;

    if (!videoUrl) {
      box.innerHTML = '<span>' + escapeHtml(data.i18n?.videoPlaceholder || 'Select a command to see the guide video area.') + '</span>';
      if (hasPersistentVideoPopup()) updatePersistentVideoPopup(false);
      return;
    }

    if (!embed) {
      box.innerHTML = '<a href="' + escapeAttr(videoUrl) + '" target="_blank" rel="noopener">' + escapeHtml(translateText('Open guide video')) + '</a>';
      if (hasPersistentVideoPopup()) updatePersistentVideoPopup(false);
      return;
    }

    const previewSrc = withEmbedParams(embed, { rel: '0', modestbranding: '1', controls: '0' });
    box.innerHTML = ''
      + '<div class="fbsa-video-preview" role="button" tabindex="0" data-fbsa-expand-video aria-label="' + escapeAttr(translateText('Open floating guide video panel')) + '">'
      + '<iframe src="' + escapeAttr(previewSrc) + '" title="' + escapeAttr(translateText('FB Software AI guide video preview')) + '" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy" tabindex="-1"></iframe>'
      + '<span class="fbsa-video-preview__glow" aria-hidden="true"></span>'
      + '<span class="fbsa-video-preview__badge">' + escapeHtml((videoBadge ? videoBadge + ' • ' : '') + translateText('Open panel player')) + '</span>'
      + '</div>';

    if (hasPersistentVideoPopup()) updatePersistentVideoPopup(false);
  }

  function toYouTubeEmbed(url) {
    try {
      const parsed = new URL(url);
      let id = '';
      if (parsed.hostname.includes('youtu.be')) {
        id = parsed.pathname.replace('/', '');
      } else if (parsed.searchParams.get('v')) {
        id = parsed.searchParams.get('v');
      } else if (parsed.pathname.includes('/embed/')) {
        return url;
      }
      return id ? 'https://www.youtube.com/embed/' + encodeURIComponent(id) : '';
    } catch (error) {
      return '';
    }
  }

  function withEmbedParams(url, params) {
    if (!url) return '';
    try {
      const parsed = new URL(url);
      Object.entries(params || {}).forEach(([key, value]) => {
        if (value === undefined || value === null || value === '') return;
        parsed.searchParams.set(key, value);
      });
      return parsed.toString();
    } catch (error) {
      return url;
    }
  }

  function getVideoPopupFeatures() {
    const width = 640;
    const height = 440;
    const left = Math.max(0, Math.round((window.screenX || window.screenLeft || 0) + (window.outerWidth || window.innerWidth || width) - width - 40));
    const top = Math.max(0, Math.round((window.screenY || window.screenTop || 0) + 90));
    return 'popup=yes,width=' + width + ',height=' + height + ',left=' + left + ',top=' + top + ',resizable=yes,scrollbars=no,menubar=no,toolbar=no,location=no,status=no';
  }

  function hasPersistentVideoPopup() {
    return !!(state.persistentVideoPopup && !state.persistentVideoPopup.closed);
  }

  function buildPersistentVideoPopupHtml(autoplaySrc) {
    const title = escapeHtml(state.currentVideoTitle || translateText('Guide Video'));
    const iframe = autoplaySrc
      ? '<iframe src="' + escapeAttr(autoplaySrc) + '" title="' + escapeAttr(translateText('FB Software AI persistent guide video')) + '" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>'
      : '<div class="fbsa-popup-empty"><a href="' + escapeAttr(state.currentVideoUrl || '#') + '" target="_blank" rel="noopener">' + escapeHtml(translateText('Open guide video')) + '</a></div>';

    return '<!doctype html>'
      + '<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
      + '<title>' + escapeHtml(translateText('FB Software AI Guide Video')) + '</title>'
      + '<style>'
      + 'html,body{margin:0;height:100%;overflow:hidden;background:#020617;color:#f8fafc;font-family:Inter,Poppins,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;}'
      + 'body{display:flex;flex-direction:column;background:radial-gradient(circle at top left,rgba(56,189,248,.18),transparent 35%),linear-gradient(145deg,rgba(15,23,42,.98),rgba(2,6,23,1));}'
      + '.fbsa-popup-header{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;border-bottom:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.88);user-select:none;}'
      + '.fbsa-popup-title{min-width:0}.fbsa-popup-title strong{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#fff;font-size:13px;font-weight:900;}.fbsa-popup-title span{display:block;margin-top:2px;color:#93c5fd;font-size:11px;font-weight:700;}'
      + '.fbsa-popup-actions{display:flex;gap:7px;flex-shrink:0}.fbsa-popup-actions button{height:31px;border:1px solid rgba(148,163,184,.28);border-radius:11px;color:#e2e8f0;background:rgba(2,6,23,.58);cursor:pointer;font-size:12px;font-weight:800;}.fbsa-popup-actions button:hover{border-color:rgba(96,165,250,.72);color:#fff;background:rgba(37,99,235,.34);}'
      + '.fbsa-popup-video{flex:1;min-height:0;background:#020617;}.fbsa-popup-video iframe{display:block;width:100%;height:100%;border:0;}.fbsa-popup-empty{display:grid;height:100%;place-items:center;padding:24px;text-align:center}.fbsa-popup-empty a{color:#93c5fd;font-weight:800;text-decoration:none;}'
      + '</style></head><body>'
      + '<header class="fbsa-popup-header"><div class="fbsa-popup-title"><strong>' + title + '</strong><span>' + escapeHtml(translateText('Keeps playing while you work in WordPress. Move this browser window anywhere.')) + '</span></div><div class="fbsa-popup-actions"><button onclick="window.resizeTo(640,440)">' + escapeHtml(translateText('Reset')) + '</button><button onclick="window.close()">' + escapeHtml(translateText('Close')) + '</button></div></header>'
      + '<main class="fbsa-popup-video">' + iframe + '</main>'
      + '</body></html>';
  }

  function updatePersistentVideoPopup(focus = false) {
    if (!hasPersistentVideoPopup()) return false;

    if (!state.currentVideoUrl) {
      try { state.persistentVideoPopup.close(); } catch (error) {}
      state.persistentVideoPopup = null;
      return false;
    }

    const popup = state.persistentVideoPopup;
    const autoplaySrc = state.currentVideoEmbed ? withEmbedParams(state.currentVideoEmbed, { rel: '0', modestbranding: '1', autoplay: '1' }) : '';

    try {
      popup.document.open();
      popup.document.write(buildPersistentVideoPopupHtml(autoplaySrc));
      popup.document.close();
      if (focus) popup.focus();
      return true;
    } catch (error) {
      state.persistentVideoPopup = null;
      return false;
    }
  }

  function openPersistentVideoPopup() {
    if (!state.currentVideoUrl) return false;

    let popup = null;
    try {
      popup = window.open('', 'fbsaGuideVideoPlayer', getVideoPopupFeatures());
    } catch (error) {
      popup = null;
    }

    if (!popup) return false;

    state.persistentVideoPopup = popup;
    return updatePersistentVideoPopup(true);
  }

  function getStoredPlayerSession() {
    try {
      const raw = window.sessionStorage ? window.sessionStorage.getItem('fbsaGuideVideoPanelSession') : '';
      if (!raw) return null;
      const parsed = JSON.parse(raw);
      if (!parsed || !parsed.url) return null;
      return parsed;
    } catch (error) {
      return null;
    }
  }

  function savePlayerSession(open = state.expandedVideoOpen) {
    try {
      if (!window.sessionStorage) return;
      if (!open || !state.playerVideoUrl) {
        window.sessionStorage.removeItem('fbsaGuideVideoPanelSession');
        return;
      }

      const player = document.getElementById('fbsa-expanded-video-player');
      const rect = player && !player.hidden ? player.getBoundingClientRect() : null;
      const stored = getStoredPlayerSession() || {};
      const time = Number(state.playerVideoCurrentTime || stored.time || state.playerVideoStartTime || 0);
      const position = rect
        ? { left: rect.left, top: rect.top }
        : (state.expandedVideoPosition || stored.position || null);
      const size = (!state.expandedVideoMini && rect)
        ? { width: rect.width, height: rect.height }
        : (state.expandedVideoSize || stored.size || null);

      window.sessionStorage.setItem('fbsaGuideVideoPanelSession', JSON.stringify({
        open: !!open,
        url: state.playerVideoUrl,
        embed: state.playerVideoEmbed,
        title: state.playerVideoTitle,
        time: Math.max(0, Math.floor(time || 0)),
        position,
        size,
        mini: !!state.expandedVideoMini,
      }));
    } catch (error) {}
  }

  function clearPlayerSession() {
    try {
      if (window.sessionStorage) window.sessionStorage.removeItem('fbsaGuideVideoPanelSession');
    } catch (error) {}
  }

  function setPlayerVideoFromCurrent() {
    state.playerVideoUrl = state.currentVideoUrl || '';
    state.playerVideoEmbed = state.currentVideoEmbed || '';
    state.playerVideoTitle = state.currentVideoTitle || 'Guide Video';
    state.playerVideoStartTime = 0;
    state.playerVideoCurrentTime = 0;
  }

  function getPanelVideoUrl() {
    return state.playerVideoUrl || state.currentVideoUrl || '';
  }

  function getPanelVideoEmbed() {
    return state.playerVideoEmbed || state.currentVideoEmbed || '';
  }

  function getPanelVideoTitle() {
    return state.playerVideoTitle || state.currentVideoTitle || 'Guide Video';
  }

  function stopYouTubeTracking() {
    if (state.youtubeTimeTimer) {
      window.clearInterval(state.youtubeTimeTimer);
      state.youtubeTimeTimer = null;
    }

    if (state.youtubePlayer && typeof state.youtubePlayer.destroy === 'function') {
      try { state.youtubePlayer.destroy(); } catch (error) {}
    }
    state.youtubePlayer = null;
  }

  function ensureYouTubeIframeApi() {
    if (window.YT && window.YT.Player) return Promise.resolve(window.YT);
    if (state.youtubeApiPromise) return state.youtubeApiPromise;

    state.youtubeApiPromise = new Promise((resolve) => {
      const previousReady = window.onYouTubeIframeAPIReady;
      window.onYouTubeIframeAPIReady = function () {
        if (typeof previousReady === 'function') {
          try { previousReady(); } catch (error) {}
        }
        resolve(window.YT);
      };

      if (!document.querySelector('script[src="https://www.youtube.com/iframe_api"]')) {
        const script = document.createElement('script');
        script.src = 'https://www.youtube.com/iframe_api';
        script.async = true;
        document.head.appendChild(script);
      }

      window.setTimeout(function () {
        if (window.YT && window.YT.Player) resolve(window.YT);
      }, 1800);
    });

    return state.youtubeApiPromise;
  }

  function initExpandedYouTubeTracking(iframeId) {
    stopYouTubeTracking();
    ensureYouTubeIframeApi().then((YT) => {
      const iframe = document.getElementById(iframeId);
      if (!iframe || !YT || !YT.Player) return;

      try {
        state.youtubePlayer = new YT.Player(iframeId, {
          events: {
            onReady: function (event) {
              state.youtubeTimeTimer = window.setInterval(function () {
                try {
                  if (!event.target || typeof event.target.getCurrentTime !== 'function') return;
                  const time = Number(event.target.getCurrentTime() || 0);
                  if (Number.isFinite(time)) {
                    state.playerVideoCurrentTime = time;
                    savePlayerSession(true);
                  }
                } catch (error) {}
              }, 1000);
            },
          },
        });
      } catch (error) {}
    });
  }

  function restoreExpandedVideoPanel() {
    const session = getStoredPlayerSession();
    if (!session || !session.open || !session.url) return;

    state.playerVideoUrl = session.url;
    state.playerVideoEmbed = session.embed || toYouTubeEmbed(session.url);
    state.playerVideoTitle = session.title || 'Guide Video';
    state.playerVideoStartTime = Number(session.time || 0);
    state.playerVideoCurrentTime = Number(session.time || 0);
    state.expandedVideoMini = !!session.mini;
    if (session.position && typeof session.position.left === 'number' && typeof session.position.top === 'number') {
      state.expandedVideoPosition = session.position;
      storeVideoPosition(session.position);
    }

    if (session.size && typeof session.size.width === 'number' && typeof session.size.height === 'number') {
      state.expandedVideoSize = session.size;
      storeVideoSize(session.size);
    }

    openExpandedVideoPlayer({ restore: true, mini: !!session.mini });
  }

  function getStoredVideoPosition() {
    try {
      const raw = window.sessionStorage ? window.sessionStorage.getItem('fbsaExpandedVideoPosition') : '';
      if (!raw) return null;
      const parsed = JSON.parse(raw);
      if (typeof parsed.left !== 'number' || typeof parsed.top !== 'number') return null;
      return parsed;
    } catch (error) {
      return null;
    }
  }

  function storeVideoPosition(position) {
    state.expandedVideoPosition = position;
    try {
      if (window.sessionStorage) {
        window.sessionStorage.setItem('fbsaExpandedVideoPosition', JSON.stringify(position));
      }
    } catch (error) {}
  }

  function getStoredVideoSize() {
    try {
      const raw = window.sessionStorage ? window.sessionStorage.getItem('fbsaExpandedVideoSize') : '';
      if (!raw) return null;
      const parsed = JSON.parse(raw);
      if (typeof parsed.width !== 'number' || typeof parsed.height !== 'number') return null;
      return parsed;
    } catch (error) {
      return null;
    }
  }

  function storeVideoSize(size) {
    state.expandedVideoSize = size;
    try {
      if (window.sessionStorage) {
        window.sessionStorage.setItem('fbsaExpandedVideoSize', JSON.stringify(size));
      }
    } catch (error) {}
  }

  function getExpandedVideoPlayer() {
    let player = document.getElementById('fbsa-expanded-video-player');
    if (player) return player;

    player = document.createElement('div');
    player.id = 'fbsa-expanded-video-player';
    player.className = 'fbsa-expanded-video-player';
    player.hidden = true;
    player.setAttribute('role', 'dialog');
    player.setAttribute('aria-modal', 'false');
    player.setAttribute('aria-label', translateText('FB Software AI expanded guide video'));
    player.setAttribute('data-fbsa-theme', getStoredWidgetTheme() || getSystemWidgetTheme());
    player.innerHTML = ''
      + '<div class="fbsa-expanded-video-player__header" data-fbsa-video-drag-handle>'
      + '<div class="fbsa-expanded-video-player__title"><strong id="fbsa-expanded-video-title">' + escapeHtml(translateText('Guide Video')) + '</strong><span>' + escapeHtml(translateText('Drag top bar • resize bottom-right corner • Close stops video')) + '</span></div>'
      + '<div class="fbsa-expanded-video-player__controls">'
      + '<button type="button" data-fbsa-video-minimize aria-label="' + escapeAttr(translateText('Make guide video smaller')) + '">' + escapeHtml(translateText('Mini')) + '</button>'
      + '<button type="button" data-fbsa-video-reset aria-label="' + escapeAttr(translateText('Reset guide video position')) + '">' + escapeHtml(translateText('Reset')) + '</button>'
      + '<button type="button" data-fbsa-video-close aria-label="' + escapeAttr(translateText('Close guide video')) + '">×</button>'
      + '</div>'
      + '</div>'
      + '<div id="fbsa-expanded-video-content" class="fbsa-expanded-video-player__content"></div>'
      + '<button type="button" class="fbsa-expanded-video-player__resize" data-fbsa-video-resize-handle aria-label="' + escapeAttr(translateText('Resize guide video')) + '"><span></span></button>';
    document.body.appendChild(player);
    return player;
  }

  function clampVideoPosition(left, top, player) {
    const margin = 12;
    const width = player.offsetWidth || 520;
    const height = player.offsetHeight || 345;
    const minLeft = margin;
    const minTop = margin;
    const maxLeft = Math.max(margin, window.innerWidth - width - margin);
    const maxTop = Math.max(margin, window.innerHeight - height - margin);

    return {
      left: Math.min(Math.max(left, minLeft), maxLeft),
      top: Math.min(Math.max(top, minTop), maxTop),
    };
  }

  function clampVideoSize(width, height, player) {
    const margin = 12;
    const rect = player ? player.getBoundingClientRect() : { left: margin, top: margin };
    const left = Number.isFinite(rect.left) ? rect.left : margin;
    const top = Number.isFinite(rect.top) ? rect.top : margin;
    const minWidth = Math.min(520, Math.max(320, window.innerWidth - margin * 2));
    const maxWidth = Math.max(minWidth, window.innerWidth - left - margin);
    const safeWidth = Math.min(Math.max(width, minWidth), maxWidth);

    /* Keep the floating guide video from becoming too short.
       The saved panel height includes the header, so preserve a 16:9 video body plus header space. */
    const headerHeight = 72;
    const minVideoHeight = Math.round(safeWidth * 9 / 16);
    const ratioMinHeight = minVideoHeight + headerHeight;
    const baseMinHeight = Math.min(548, Math.max(320, window.innerHeight - margin * 2));
    const maxHeight = Math.max(baseMinHeight, window.innerHeight - top - margin);
    const wantedMinHeight = Math.max(baseMinHeight, ratioMinHeight);
    const safeHeight = Math.min(Math.max(height, wantedMinHeight), maxHeight);

    return {
      width: safeWidth,
      height: safeHeight,
    };
  }

  function applyExpandedVideoSize(player, size, persist = true) {
    if (!player || !size || state.expandedVideoMini) return;
    const safeSize = clampVideoSize(Number(size.width || 0), Number(size.height || 0), player);
    player.style.width = safeSize.width + 'px';
    player.style.height = safeSize.height + 'px';
    player.classList.add('fbsa-expanded-video-player--resized');
    if (persist) storeVideoSize(safeSize);
  }

  function clearExpandedVideoInlineSize(player) {
    if (!player) return;
    player.style.width = '';
    player.style.height = '';
    player.classList.remove('fbsa-expanded-video-player--resized');
  }

  function getDefaultVideoPosition(player) {
    const margin = 22;
    const widget = document.getElementById('fbsa-floating-widget');
    const width = player.offsetWidth || 860;
    const height = player.offsetHeight || 548;
    let left = window.innerWidth - width - margin;
    let top = window.innerHeight - height - margin;

    if (widget) {
      const rect = widget.getBoundingClientRect();
      const candidateLeft = rect.left - width - 16;
      if (candidateLeft >= margin) {
        left = candidateLeft;
        top = Math.max(margin, Math.min(rect.top, window.innerHeight - height - margin));
      }
    }

    return clampVideoPosition(left, top, player);
  }

  function positionExpandedVideoPlayer(player, position, persist = true) {
    const safePosition = clampVideoPosition(position.left, position.top, player);
    player.style.left = safePosition.left + 'px';
    player.style.top = safePosition.top + 'px';
    player.style.right = 'auto';
    player.style.bottom = 'auto';
    if (persist) storeVideoPosition(safePosition);
  }

  function updateExpandedVideoContent() {
    const player = getExpandedVideoPlayer();
    const title = $('#fbsa-expanded-video-title', player);
    const content = $('#fbsa-expanded-video-content', player);
    const videoUrl = getPanelVideoUrl();
    const embed = getPanelVideoEmbed();
    const videoTitle = getPanelVideoTitle();

    if (title) title.textContent = videoTitle;
    if (!content) return;

    if (!videoUrl) {
      stopYouTubeTracking();
      content.innerHTML = '<div class="fbsa-expanded-video-player__empty">' + escapeHtml(translateText('Select a command to load its guide video.')) + '</div>';
      return;
    }

    if (!embed) {
      stopYouTubeTracking();
      content.innerHTML = '<div class="fbsa-expanded-video-player__empty"><a href="' + escapeAttr(videoUrl) + '" target="_blank" rel="noopener">' + escapeHtml(translateText('Open guide video')) + '</a></div>';
      return;
    }

    const iframeId = 'fbsa-expanded-youtube-' + Date.now();
    const start = Math.max(0, Math.floor(Number(state.playerVideoStartTime || state.playerVideoCurrentTime || 0)));
    const expandedSrc = withEmbedParams(embed, {
      rel: '0',
      modestbranding: '1',
      autoplay: '1',
      enablejsapi: '1',
      origin: window.location.origin,
      start: start > 1 ? start : '',
    });

    content.innerHTML = '<iframe id="' + escapeAttr(iframeId) + '" src="' + escapeAttr(expandedSrc) + '" title="FB Software AI floating guide video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';
    initExpandedYouTubeTracking(iframeId);
    savePlayerSession(true);
  }

  function openExpandedVideoPlayer(options = {}) {
    const player = getExpandedVideoPlayer();

    if (!options.restore) {
      setPlayerVideoFromCurrent();
    }

    if (!getPanelVideoUrl()) return false;

    updateExpandedVideoContent();
    player.hidden = false;
    player.classList.add('fbsa-expanded-video-player--open');
    state.expandedVideoOpen = true;
    state.expandedVideoMini = !!options.mini;
    player.classList.toggle('fbsa-expanded-video-player--mini', state.expandedVideoMini);
    const miniButton = $('[data-fbsa-video-minimize]', player);
    if (miniButton) miniButton.textContent = translateText(state.expandedVideoMini ? 'Full' : 'Mini');

    if (state.expandedVideoMini) {
      clearExpandedVideoInlineSize(player);
    } else {
      const storedSize = state.expandedVideoSize || getStoredVideoSize();
      if (storedSize) applyExpandedVideoSize(player, storedSize, false);
    }

    const stored = state.expandedVideoPosition || getStoredVideoPosition();
    positionExpandedVideoPlayer(player, stored || getDefaultVideoPosition(player), false);
    savePlayerSession(true);
    return true;
  }

  function minimizeExpandedVideoPlayer() {
    const player = getExpandedVideoPlayer();
    if (!state.expandedVideoOpen || player.hidden) return;

    if (!state.expandedVideoMini) {
      const fullRect = player.getBoundingClientRect();
      storeVideoSize({ width: fullRect.width, height: fullRect.height });
    }

    state.expandedVideoMini = !state.expandedVideoMini;
    player.classList.toggle('fbsa-expanded-video-player--mini', state.expandedVideoMini);
    const button = $('[data-fbsa-video-minimize]', player);
    if (button) button.textContent = translateText(state.expandedVideoMini ? 'Full' : 'Mini');

    if (state.expandedVideoMini) {
      clearExpandedVideoInlineSize(player);
    } else {
      const storedSize = state.expandedVideoSize || getStoredVideoSize();
      if (storedSize) applyExpandedVideoSize(player, storedSize, false);
    }

    const rect = player.getBoundingClientRect();
    positionExpandedVideoPlayer(player, { left: rect.left, top: rect.top }, true);
    savePlayerSession(true);
  }

  function closeExpandedVideoPlayer(clearContent = true) {
    const player = getExpandedVideoPlayer();
    player.hidden = true;
    player.classList.remove('fbsa-expanded-video-player--open');
    player.classList.remove('fbsa-expanded-video-player--mini');
    state.expandedVideoOpen = false;
    state.expandedVideoMini = false;
    state.playerVideoUrl = '';
    state.playerVideoEmbed = '';
    state.playerVideoTitle = 'Guide Video';
    state.playerVideoStartTime = 0;
    state.playerVideoCurrentTime = 0;
    stopYouTubeTracking();
    clearPlayerSession();
    if (clearContent) {
      const content = $('#fbsa-expanded-video-content', player);
      if (content) content.innerHTML = '';
    }
  }

  function resetExpandedVideoPosition() {
    const player = getExpandedVideoPlayer();
    clearExpandedVideoInlineSize(player);
    state.expandedVideoSize = null;
    try {
      if (window.sessionStorage) {
        window.sessionStorage.removeItem('fbsaExpandedVideoSize');
      }
    } catch (error) {}
    positionExpandedVideoPlayer(player, getDefaultVideoPosition(player), true);
    savePlayerSession(true);
  }

  function captureExpandedVideoPanelRect() {
    const player = document.getElementById('fbsa-expanded-video-player');
    if (!player || player.hidden || !state.expandedVideoOpen) return null;
    const rect = player.getBoundingClientRect();
    return {
      left: rect.left,
      top: rect.top,
      width: rect.width,
      height: rect.height,
      mini: !!state.expandedVideoMini,
      resized: player.classList.contains('fbsa-expanded-video-player--resized'),
    };
  }

  function restoreExpandedVideoPanelRect(snapshot) {
    if (!snapshot || !state.expandedVideoOpen) return;
    const player = getExpandedVideoPlayer();
    if (!player || player.hidden) return;

    if (!snapshot.mini) {
      applyExpandedVideoSize(player, { width: snapshot.width, height: snapshot.height }, true);
    }

    positionExpandedVideoPlayer(player, { left: snapshot.left, top: snapshot.top }, true);
    savePlayerSession(true);
  }

  function initExpandableGuideVideo() {
    let dragState = null;
    let resizeState = null;

    document.addEventListener('click', function (event) {
      const adminLink = event.target.closest('a[href]');
      if (adminLink && state.expandedVideoOpen && !event.defaultPrevented && event.button === 0 && !event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey && adminLink.target !== '_blank' && !adminLink.closest('#fbsa-floating-widget') && !adminLink.closest('#fbsa-expanded-video-player')) {
        const href = adminLink.getAttribute('href');
        if (isSafeSoftAdminUrl(href)) {
          event.preventDefault();
          softNavigateAdminUrl(href);
          return;
        }
      }

      const expandTarget = event.target.closest('[data-fbsa-expand-video]');
      if (expandTarget) {
        event.preventDefault();
        openExpandedVideoPlayer();
        return;
      }

      if (event.target.closest('[data-fbsa-video-minimize]')) {
        event.preventDefault();
        minimizeExpandedVideoPlayer();
        return;
      }

      if (event.target.closest('[data-fbsa-video-close]')) {
        event.preventDefault();
        closeExpandedVideoPlayer(true);
        return;
      }

      if (event.target.closest('[data-fbsa-video-reset]')) {
        event.preventDefault();
        resetExpandedVideoPosition();
      }
    });

    document.addEventListener('keydown', function (event) {
      if ((event.key === 'Enter' || event.key === ' ') && event.target.closest('[data-fbsa-expand-video]')) {
        event.preventDefault();
        openExpandedVideoPlayer();
        return;
      }

      if (event.key === 'Escape' && state.expandedVideoOpen) {
        closeExpandedVideoPlayer(true);
      }
    });

    document.addEventListener('pointerdown', function (event) {
      const resizeHandle = event.target.closest('[data-fbsa-video-resize-handle]');
      const resizePlayer = event.target.closest('#fbsa-expanded-video-player');
      if (resizeHandle && resizePlayer && !resizePlayer.hidden && !state.expandedVideoMini) {
        const rect = resizePlayer.getBoundingClientRect();
        resizeState = {
          player: resizePlayer,
          startX: event.clientX,
          startY: event.clientY,
          startWidth: rect.width,
          startHeight: rect.height,
        };
        document.body.classList.add('fbsa-video-resizing');
        resizeHandle.setPointerCapture?.(event.pointerId);
        event.preventDefault();
        event.stopPropagation();
        return;
      }

      const handle = event.target.closest('[data-fbsa-video-drag-handle]');
      const player = event.target.closest('#fbsa-expanded-video-player');
      if (!handle || !player || player.hidden || event.target.closest('button, a')) return;

      const rect = player.getBoundingClientRect();
      dragState = {
        player,
        offsetX: event.clientX - rect.left,
        offsetY: event.clientY - rect.top,
      };

      document.body.classList.add('fbsa-video-dragging');
      handle.setPointerCapture?.(event.pointerId);
      event.preventDefault();
    });

    document.addEventListener('pointermove', function (event) {
      if (resizeState) {
        const nextSize = clampVideoSize(
          resizeState.startWidth + (event.clientX - resizeState.startX),
          resizeState.startHeight + (event.clientY - resizeState.startY),
          resizeState.player
        );
        applyExpandedVideoSize(resizeState.player, nextSize, false);
        state.expandedVideoSize = nextSize;
        event.preventDefault();
        return;
      }

      if (!dragState) return;
      const next = clampVideoPosition(event.clientX - dragState.offsetX, event.clientY - dragState.offsetY, dragState.player);
      dragState.player.style.left = next.left + 'px';
      dragState.player.style.top = next.top + 'px';
      dragState.player.style.right = 'auto';
      dragState.player.style.bottom = 'auto';
      state.expandedVideoPosition = next;
      event.preventDefault();
    });

    document.addEventListener('pointerup', function () {
      if (resizeState) {
        const rect = resizeState.player.getBoundingClientRect();
        storeVideoSize({ width: rect.width, height: rect.height });
        savePlayerSession(true);
        document.body.classList.remove('fbsa-video-resizing');
        resizeState = null;
      }

      if (!dragState) return;
      const rect = dragState.player.getBoundingClientRect();
      storeVideoPosition({ left: rect.left, top: rect.top });
      savePlayerSession(true);
      document.body.classList.remove('fbsa-video-dragging');
      dragState = null;
    });


    window.addEventListener('popstate', function () {
      if (!state.expandedVideoOpen || !isSafeSoftAdminUrl(window.location.href)) return;
      softNavigateAdminUrl(window.location.href, { push: false });
    });

    window.addEventListener('beforeunload', function () {
      if (state.expandedVideoOpen) savePlayerSession(true);
    });

    window.addEventListener('resize', function () {
      applyFloatingWidgetViewportSizing();

      if (!state.expandedVideoOpen) return;
      const player = getExpandedVideoPlayer();
      if (!state.expandedVideoMini && state.expandedVideoSize) {
        applyExpandedVideoSize(player, state.expandedVideoSize, true);
      }
      const rect = player.getBoundingClientRect();
      positionExpandedVideoPlayer(player, { left: rect.left, top: rect.top }, true);
      savePlayerSession(true);
    });
  }



  function isSafeSoftAdminUrl(rawUrl) {
    if (!rawUrl || !state.expandedVideoOpen) return false;

    try {
      const url = new URL(rawUrl, window.location.href);
      const adminBase = new URL(data.adminUrl || '/wp-admin/', window.location.origin);

      if (url.origin !== window.location.origin) return false;
      if (url.hash && url.pathname === window.location.pathname && url.search === window.location.search) return false;
      if (!url.pathname.startsWith(adminBase.pathname)) return false;

      const params = url.searchParams;
      const action = String(params.get('action') || '').toLowerCase();
      const unsafeActions = ['delete', 'trash', 'untrash', 'activate', 'deactivate', 'install-plugin', 'upload-plugin', 'update', 'logout'];
      if (params.has('_wpnonce') || params.has('_wp_http_referer')) return false;
      if (action && unsafeActions.some((item) => action.includes(item))) return false;

      const path = normalizeAdminPath(url.toString());
      const unsafeFiles = [
        'admin-ajax.php',
        'async-upload.php',
        'admin-post.php',
        'update.php',
        'update-core.php',
        // Block editor / site editor screens load many page-specific scripts.
        // They must use normal WordPress navigation, otherwise Gutenberg can show
        // “The block editor requires JavaScript” after soft navigation.
        'post-new.php',
        'post.php',
        'site-editor.php',
        'widgets.php',
        'customize.php'
      ];
      if (unsafeFiles.some((file) => path.indexOf(file) === 0)) return false;

      return true;
    } catch (error) {
      return false;
    }
  }

  function setSoftNavigationBusy(isBusy) {
    document.body.classList.toggle('fbsa-soft-navigation-busy', !!isBusy);
    const player = document.getElementById('fbsa-expanded-video-player');
    if (player) player.classList.toggle('fbsa-expanded-video-player--navigating', !!isBusy);
  }

  function updateWidgetForCurrentAdminPage() {
    const autoGuideCommand = applyCurrentAdminPageGuide();
    syncSelectValues();
    renderSubcategories();
    renderCommands();
    syncSelectValues();
    renderCommandAction(autoGuideCommand || null);
    if (!autoGuideCommand) updateVideo(null);
  }

  function replaceNodeFromFetchedDocument(selector, fetchedDocument) {
    const current = document.querySelector(selector);
    const next = fetchedDocument.querySelector(selector);
    if (!current || !next) return false;
    current.replaceWith(document.importNode(next, true));
    return true;
  }

  function softNavigateAdminUrl(rawUrl, options = {}) {
    if (!isSafeSoftAdminUrl(rawUrl)) {
      window.location.href = rawUrl;
      return Promise.resolve(false);
    }

    const url = new URL(rawUrl, window.location.href);
    const videoPanelSnapshot = captureExpandedVideoPanelRect();
    setSoftNavigationBusy(true);
    savePlayerSession(true);

    return fetch(url.toString(), {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        'X-FBSA-Soft-Navigation': '1',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })
      .then((response) => {
        if (!response.ok) throw new Error('Navigation request failed.');
        return response.text();
      })
      .then((html) => {
        const parsed = new DOMParser().parseFromString(html, 'text/html');
        const nextContent = parsed.querySelector('#wpbody-content');
        if (!nextContent) throw new Error('Could not find WordPress admin content.');

        const currentContent = document.querySelector('#wpbody-content');
        if (!currentContent) throw new Error('Could not find current WordPress admin content.');
        currentContent.replaceWith(document.importNode(nextContent, true));

        replaceNodeFromFetchedDocument('#adminmenuwrap', parsed);
        replaceNodeFromFetchedDocument('#adminmenuback', parsed);
        replaceNodeFromFetchedDocument('#wpadminbar', parsed);

        if (parsed.title) document.title = parsed.title;
        if (parsed.body && parsed.body.className) document.body.className = parsed.body.className + ' fbsa-soft-navigated';

        if (options.push !== false) {
          window.history.pushState({ fbsaSoftNavigation: true }, parsed.title || '', url.toString());
        }

        updateWidgetForCurrentAdminPage();
        restoreExpandedVideoPanelRect(videoPanelSnapshot);
        loadStatus();
        savePlayerSession(true);
        return true;
      })
      .catch(() => {
        window.location.href = url.toString();
        return false;
      })
      .finally(() => {
        setSoftNavigationBusy(false);
      });
  }


  function normalizeAdminPath(path) {
    if (!path) return '';

    try {
      const adminBase = new URL(data.adminUrl || '/wp-admin/', window.location.origin);
      const raw = String(path);
      const parsed = /^https?:\/\//i.test(raw) ? new URL(raw) : new URL(raw.replace(/^\/+/, ''), adminBase);
      let relative = parsed.pathname || '';
      const basePath = adminBase.pathname.endsWith('/') ? adminBase.pathname : adminBase.pathname + '/';

      if (relative.indexOf(basePath) === 0) {
        relative = relative.slice(basePath.length);
      } else {
        relative = relative.replace(/^\/+/, '');
      }

      relative = relative.replace(/^\/+/, '');
      if (!relative) relative = 'index.php';
      return relative + (parsed.search || '');
    } catch (error) {
      return String(path).replace(/^\/+/, '').split('#')[0];
    }
  }

  function adminPathMatches(currentPath, commandPath) {
    const current = normalizeAdminPath(currentPath);
    const target = normalizeAdminPath(commandPath);
    if (!current || !target) return false;
    if (current === target) return true;

    const currentParts = current.split('?');
    const targetParts = target.split('?');
    const currentFile = currentParts[0];
    const targetFile = targetParts[0];
    const currentQuery = currentParts[1] || '';
    const targetQuery = targetParts[1] || '';

    if (currentFile !== targetFile || !targetQuery) {
      return false;
    }

    const currentParams = new URLSearchParams(currentQuery);
    const targetParams = new URLSearchParams(targetQuery);
    for (const [key, value] of targetParams.entries()) {
      if (currentParams.get(key) !== value) {
        return false;
      }
    }
    return true;
  }

  function findCurrentAdminPageCommand() {
    const currentPath = normalizeAdminPath(window.location.href);
    if (!currentPath) return null;

    for (const category of workflow.categories || []) {
      for (const command of category.commands || []) {
        if (command && command.type === 'navigate' && command.adminPath && adminPathMatches(currentPath, command.adminPath)) {
          return { category, subcategory: null, command };
        }
      }

      for (const subcategory of category.subcategories || []) {
        for (const command of subcategory.commands || []) {
          if (command && command.type === 'navigate' && command.adminPath && adminPathMatches(currentPath, command.adminPath)) {
            return { category, subcategory, command };
          }
        }
      }
    }

    return null;
  }

  function applyCurrentAdminPageGuide() {
    const match = findCurrentAdminPageCommand();
    if (!match) return null;

    state.selectedCategory = match.category.id;
    state.selectedSubcategory = match.subcategory ? match.subcategory.id : (categoryNeedsSubcategory(match.category) ? '__root' : '');
    state.selectedCommand = match.command;
    return match.command;
  }

  function syncSelectValues() {
    const categorySelect = $('#fbsa-category-select');
    const subcategorySelect = $('#fbsa-subcategory-select');
    const commandSelect = $('#fbsa-command-select');

    if (categorySelect) {
      categorySelect.value = state.selectedCategory || '';
    }

    if (subcategorySelect) {
      subcategorySelect.value = state.selectedSubcategory || '';
    }

    if (commandSelect) {
      commandSelect.value = state.selectedCommand ? state.selectedCommand.id : '';
    }
  }

  function escapeHtml(value) {
    return String(value || '').replace(/[&<>"]/g, function (match) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[match];
    });
  }

  function escapeAttr(value) {
    return escapeHtml(value).replace(/'/g, '&#039;');
  }

  function renderCategories() {
    const select = $('#fbsa-category-select');
    if (!select) return;

    select.innerHTML = '<option value="">' + escapeHtml(translateText('Start Building')) + '</option>';
    if (workflowLoadError && !(workflow.categories || []).length) {
      select.innerHTML = '<option value="">' + escapeHtml('Workflow unavailable') + '</option>';
      select.disabled = true;
      return;
    }
    for (const category of workflow.categories || []) {
      const option = document.createElement('option');
      option.value = category.id;
      option.textContent = category.label;
      select.appendChild(option);
    }
  }

  function renderSubcategories() {
    const field = $('#fbsa-subcategory-field');
    const select = $('#fbsa-subcategory-select');
    const category = findCategory(state.selectedCategory);
    if (!field || !select) return;

    const subcategories = category && Array.isArray(category.subcategories) ? category.subcategories : [];
    const rootCommands = getRootCommands(category);

    select.innerHTML = '<option value="">' + escapeHtml(translateText('Choose a sub category')) + '</option>';
    select.disabled = true;
    field.hidden = true;

    if (!category || !subcategories.length) {
      state.selectedSubcategory = '';
      return;
    }

    field.hidden = false;
    select.disabled = false;

    if (rootCommands.length) {
      const rootOption = document.createElement('option');
      rootOption.value = '__root';
      rootOption.textContent = category.rootCommandLabel || translateText('Backend Shortcuts');
      select.appendChild(rootOption);
    }

    for (const subcategory of subcategories) {
      const option = document.createElement('option');
      option.value = subcategory.id;
      option.textContent = subcategory.label;
      select.appendChild(option);
    }

    const availableValues = Array.from(select.options).map((option) => option.value);
    if (!availableValues.includes(state.selectedSubcategory)) {
      state.selectedSubcategory = '';
    }
    select.value = state.selectedSubcategory || '';
  }

  function categoryNeedsSubcategory(category) {
    return !!(category && Array.isArray(category.subcategories) && category.subcategories.length);
  }

  function updateCommandFieldVisibility(category) {
    const field = $('#fbsa-command-field');
    if (!field) return;
    field.hidden = !category || (categoryNeedsSubcategory(category) && !state.selectedSubcategory);
  }

  function renderCommands() {
    const select = $('#fbsa-command-select');
    const category = findCategory(state.selectedCategory);
    updateCommandFieldVisibility(category);
    if (!select) return;

    state.sectionCompletionMessage = '';
    select.innerHTML = '<option value="">' + (data.i18n?.chooseCommand || 'Choose a command') + '</option>';
    select.disabled = !category;

    if (!category) {
      return;
    }

    const commands = getActiveCommands(category);
    if (Array.isArray(category.subcategories) && category.subcategories.length && !state.selectedSubcategory) {
      select.disabled = true;
      return;
    }

    for (const command of commands || []) {
      if (isHiddenCommand(command)) {
        continue;
      }
      const option = document.createElement('option');
      option.value = command.id;
      option.textContent = command.label;
      select.appendChild(option);
    }

    if (select.options.length === 1) {
      const completionMessage = buildSectionCompletionMessage(commands);
      const displayMessage = getSectionCompletionSelectLabel(category, commands, completionMessage);
      state.sectionCompletionMessage = displayMessage;
      select.innerHTML = '';
      const option = document.createElement('option');
      option.value = '';
      option.textContent = displayMessage;
      option.selected = true;
      option.className = 'fbsa-complete-option';
      select.appendChild(option);
      select.value = '';
      select.disabled = false;
      setMessage(displayMessage, 'complete');
      updateVideo(null);
    }
  }

  function renderCommandAction(command) {
    const button = $('#fbsa-action-button');
    if (!button) return;

    state.selectedCommand = command || null;
    button.hidden = true;
    button.disabled = false;
    button.textContent = translateText('Create');

    if (!command) {
      if (state.sectionCompletionMessage) {
        setMessage(state.sectionCompletionMessage, 'complete');
      } else if (!state.selectedCategory) {
        setMessage(workflowLoadError || 'Select a workspace command to begin.', workflowLoadError ? 'error' : '');
      } else {
        const category = findCategory(state.selectedCategory);
        setMessage(categoryNeedsSubcategory(category) && !state.selectedSubcategory ? 'Choose a sub category to continue.' : 'Choose a command to continue.');
      }
      updateVideo(null);
      return;
    }

    setMessage(command.message || 'Ready. Click the button to continue.');
    updateVideo(command);

    if (command.type === 'create_page' || command.type === 'create_post' || command.type === 'install_theme' || command.type === 'install_plugin') {
      button.hidden = false;
      button.textContent = command.buttonLabel || translateText(command.type === 'install_theme' ? 'Install Theme' : (command.type === 'install_plugin' ? 'Install Plugin' : 'Create'));
      return;
    }

    if (command.type === 'navigate') {
      button.hidden = false;
      button.textContent = command.buttonLabel || translateText('Open');
    }
  }

  function updateProgress(status) {
    if (!status) return;

    const percent = Number(status.percent || 0);
    const percentText = $('#fbsa-progress-percent');
    const fill = $('#fbsa-progress-fill');
    const bar = $('.fbsa-progress-bar');
    const text = $('#fbsa-progress-text');

    if (percentText) percentText.textContent = percent + '%';
    if (fill) fill.style.width = percent + '%';
    if (bar) bar.setAttribute('aria-valuenow', String(percent));
    if (text) {
      text.textContent = translateText((status.completed || 0) + ' of ' + (status.total || 0) + ' core pages completed.');
    }

    state.completedIds = new Set((status.completedCommands || []).map((item) => item.commandId));
    state.installedPluginSlugs = new Set(status.installedPlugins || Array.from(state.installedPluginSlugs));
    state.installedThemeSlugs = new Set(status.installedThemes || Array.from(state.installedThemeSlugs));
    state.activePluginSlugs = new Set(status.activePlugins || Array.from(state.activePluginSlugs));
  }

  function postAjax(payload) {
    const body = new URLSearchParams();
    Object.entries(payload).forEach(([key, value]) => body.append(key, value));

    return fetch(data.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString(),
    }).then((response) => response.json());
  }

  function loadStatus() {
    if (!data.ajaxUrl || !data.nonce) return;
    postAjax({ action: 'fbsa_get_status', nonce: data.nonce })
      .then((response) => {
        if (response && response.success) {
          updateProgress(response.data);
          renderCommands();
        }
      })
      .catch(() => {});
  }

  function runSelectedCommand() {
    if (state.isBusy || !state.selectedCommand) return;
    const command = state.selectedCommand;

    if (command.type === 'navigate') {
      const targetUrl = buildAdminUrl(resolveCommandAdminPath(command));
      // Complex editor screens must load normally so WordPress can enqueue all editor scripts.
      // The video panel session is saved before the reload and restored after the new page loads.
      if (!isSafeSoftAdminUrl(targetUrl)) {
        if (state.expandedVideoOpen) savePlayerSession(true);
        window.location.href = targetUrl;
        return;
      }
      softNavigateAdminUrl(targetUrl);
      return;
    }

    if (command.type !== 'create_page' && command.type !== 'create_post' && command.type !== 'install_theme' && command.type !== 'install_plugin') {
      return;
    }

    state.isBusy = true;
    const button = $('#fbsa-action-button');
    if (button) {
      button.disabled = true;
      button.textContent = data.i18n?.loading || 'Working...';
    }
    setMessage(command.type === 'install_theme' ? 'Installing theme now...' : (command.type === 'install_plugin' ? 'Installing plugin now...' : 'Creating content now...'), 'info');

    postAjax({
      action: (command.type === 'install_theme' || command.type === 'install_plugin') ? 'fbsa_run_command' : 'fbsa_create_content',
      nonce: data.nonce,
      commandId: command.id,
    })
      .then((response) => {
        if (!response || !response.success) {
          throw new Error(response?.data?.message || data.i18n?.error || 'Something went wrong.');
        }

        if (command.type === 'install_plugin' && command.pluginSlug) {
          state.installedPluginSlugs.add(command.pluginSlug);
          if (response.data.activated) {
            state.activePluginSlugs.add(command.pluginSlug);
          }
        }

        if (command.type === 'install_theme' && command.themeSlug) {
          state.installedThemeSlugs.add(command.themeSlug);
        }

        updateProgress(response.data.status);
        setMessage(response.data.message || (data.i18n?.done || 'Done'), 'success');
        renderCommands();

        if (response.data.redirectUrl) {
          window.location.href = response.data.redirectUrl;
        }
      })
      .catch((error) => {
        setMessage(error.message || data.i18n?.error || 'Something went wrong.', 'error');
        if (button) {
          button.disabled = false;
          button.textContent = command.buttonLabel || 'Create';
        }
      })
      .finally(() => {
        state.isBusy = false;
      });
  }



  function initSettingsTabs() {
    const tabs = Array.from(document.querySelectorAll('[data-fbsa-settings-tab]'));
    const panels = Array.from(document.querySelectorAll('[data-fbsa-settings-panel]'));
    if (!tabs.length || !panels.length) return;

    const storageKey = 'fbsaSettingsActiveTab';
    const validIds = new Set(tabs.map((tab) => tab.getAttribute('data-fbsa-settings-tab')));

    function getRequestedTab() {
      const hashValue = window.location.hash.replace(/^#fbsa-settings-/, '');
      if (validIds.has(hashValue)) return hashValue;
      try {
        const saved = window.sessionStorage.getItem(storageKey);
        if (validIds.has(saved)) return saved;
      } catch (error) {}
      return 'overview';
    }

    function activateTab(tabId, options) {
      const settings = options || {};
      const nextId = validIds.has(tabId) ? tabId : 'overview';

      tabs.forEach((tab) => {
        const active = tab.getAttribute('data-fbsa-settings-tab') === nextId;
        tab.classList.toggle('is-active', active);
        tab.setAttribute('aria-selected', active ? 'true' : 'false');
        tab.tabIndex = active ? 0 : -1;
      });

      panels.forEach((panel) => {
        const active = panel.getAttribute('data-fbsa-settings-panel') === nextId;
        panel.hidden = !active;
        panel.classList.toggle('is-active', active);
      });

      try {
        window.sessionStorage.setItem(storageKey, nextId);
      } catch (error) {}

      if (!settings.skipHash && window.history && typeof window.history.replaceState === 'function') {
        window.history.replaceState(null, '', '#fbsa-settings-' + nextId);
      }
    }

    tabs.forEach((tab, index) => {
      tab.addEventListener('click', () => activateTab(tab.getAttribute('data-fbsa-settings-tab')));
      tab.addEventListener('keydown', (event) => {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
        event.preventDefault();
        let nextIndex = index;
        if (event.key === 'ArrowLeft') nextIndex = (index - 1 + tabs.length) % tabs.length;
        if (event.key === 'ArrowRight') nextIndex = (index + 1) % tabs.length;
        if (event.key === 'Home') nextIndex = 0;
        if (event.key === 'End') nextIndex = tabs.length - 1;
        tabs[nextIndex].focus();
        activateTab(tabs[nextIndex].getAttribute('data-fbsa-settings-tab'));
      });
    });

    activateTab(getRequestedTab(), { skipHash: true });
  }

  function initSettingsCustomCommandPanel() {
    const toggle = $('#fbsa-custom-command-toggle');
    const panel = $('#fbsa-custom-command-panel');
    if (!toggle || !panel) return;

    function setOpen(isOpen) {
      panel.hidden = !isOpen;
      panel.classList.toggle('fbsa-custom-command-form--open', isOpen);
      panel.classList.toggle('fbsa-custom-command-form--collapsed', !isOpen);
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      toggle.textContent = translateText(isOpen ? 'Close Custom Command' : 'Add Custom Command');
    }

    setOpen(false);

    toggle.addEventListener('click', function () {
      setOpen(panel.hidden);
      if (panel.hidden === false) {
        const firstInput = $('#fbsa_command_label', panel);
        if (firstInput) {
          setTimeout(() => firstInput.focus(), 60);
        }
      }
    });
  }


  function initWelcomeBannerUploader() {
    const uploadButton = document.querySelector('[data-fbsa-banner-upload]');
    const removeButton = document.querySelector('[data-fbsa-banner-remove]');
    const idInput = document.getElementById('fbsa_welcome_banner_id');
    const urlInput = document.getElementById('fbsa_welcome_banner_url');
    const preview = document.querySelector('[data-fbsa-banner-preview]');
    if (!uploadButton || !idInput || !urlInput || !preview || uploadButton.disabled) return;

    let mediaFrame = null;

    function renderPreview(url) {
      preview.innerHTML = '';
      if (!url) {
        preview.classList.add('is-empty');
        preview.innerHTML = '<span class="dashicons dashicons-format-image" aria-hidden="true"></span>'
          + '<strong>No banner selected</strong>'
          + '<small>Recommended size: 1600 × 500 px or wider.</small>';
        return;
      }
      preview.classList.remove('is-empty');
      const image = document.createElement('img');
      image.src = url;
      image.alt = translateText('Selected FB Software AI welcome banner');
      preview.appendChild(image);
    }

    uploadButton.addEventListener('click', function () {
      if (!window.wp || !window.wp.media) return;
      if (mediaFrame) {
        mediaFrame.open();
        return;
      }

      mediaFrame = window.wp.media({
        title: translateText('Choose FB Software AI Welcome Banner'),
        button: { text: translateText('Use this banner') },
        library: { type: 'image' },
        multiple: false,
      });

      mediaFrame.on('select', function () {
        const selection = mediaFrame.state().get('selection').first();
        if (!selection) return;
        const attachment = selection.toJSON();
        idInput.value = attachment.id || 0;
        urlInput.value = attachment.url || '';
        renderPreview(attachment.url || '');
      });

      mediaFrame.open();
    });

    if (removeButton && !removeButton.disabled) {
      removeButton.addEventListener('click', function () {
        idInput.value = '0';
        urlInput.value = '';
        renderPreview('');
      });
    }
  }

  function initDashboardWelcomeScreenOption() {
    if (!document.body.classList.contains('fbsa-dashboard-welcome-active')) return;
    const checkbox = document.getElementById('wp_welcome_panel-hide');
    if (!checkbox || !checkbox.parentElement) return;

    const label = checkbox.parentElement;
    Array.from(label.childNodes).forEach(function (node) {
      if (node.nodeType === Node.TEXT_NODE && node.nodeValue && node.nodeValue.trim() === 'Welcome') {
        node.nodeValue = ' ' + translateText('FB Software AI Welcome');
      }
    });
  }

  function initConfirmableActions() {
    const modal = $('#fbsa-confirm-modal');
    const title = $('#fbsa-confirm-modal-title');
    const messageBox = $('#fbsa-confirm-modal-message');
    const yesButton = $('#fbsa-confirm-modal-yes');
    let pendingButton = null;

    function closeModal() {
      if (!modal) return;
      modal.hidden = true;
      modal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('fbsa-confirm-modal-open');
      pendingButton = null;
    }

    function submitPendingAction() {
      if (!pendingButton) return closeModal();
      const button = pendingButton;
      const form = button.form;
      closeModal();
      if (!form) return;

      if (typeof form.requestSubmit === 'function') {
        form.requestSubmit(button);
        return;
      }

      if (button.name) {
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = button.name;
        hidden.value = button.value || '1';
        form.appendChild(hidden);
      }
      form.submit();
    }

    function openModal(button, confirmMessage) {
      const actionLabel = button.getAttribute('data-fbsa-confirm-action') || 'Yes, continue';
      pendingButton = button;
      if (title) title.textContent = translateText(button.classList.contains('fbsa-theme-deactivate-button') ? 'Deactivate theme?' : 'Confirm action');
      if (messageBox) messageBox.textContent = confirmMessage;
      if (yesButton) yesButton.textContent = actionLabel;
      modal.hidden = false;
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('fbsa-confirm-modal-open');
      if (yesButton) setTimeout(() => yesButton.focus(), 30);
    }

    document.addEventListener('click', function (event) {
      const cancel = event.target.closest('[data-fbsa-confirm-cancel]');
      if (cancel) {
        event.preventDefault();
        closeModal();
        return;
      }

      if (event.target === yesButton) {
        event.preventDefault();
        submitPendingAction();
        return;
      }

      const button = event.target.closest('[data-fbsa-confirm]');
      if (!button) return;

      const confirmMessage = button.getAttribute('data-fbsa-confirm') || 'Are you sure?';
      if (!modal) {
        if (!window.confirm(confirmMessage)) {
          event.preventDefault();
          event.stopPropagation();
        }
        return;
      }

      event.preventDefault();
      event.stopPropagation();
      openModal(button, confirmMessage);
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && modal && !modal.hidden) {
        closeModal();
      }
    });
  }


  function initAdminSidebarHoverHold() {
    const body = document.body;
    if (!body || !body.classList.contains('fbsa-admin-ui-themed')) return;

    const adminMenu = document.getElementById('adminmenu');
    if (!adminMenu) return;

    const timers = new WeakMap();
    const topItems = adminMenu.querySelectorAll('li.menu-top.wp-has-submenu, li.menu-top.wp-has-current-submenu');

    topItems.forEach(function (item) {
      const submenu = item.querySelector(':scope > .wp-submenu');
      if (!submenu) return;

      function openHold() {
        const existingTimer = timers.get(item);
        if (existingTimer) {
          window.clearTimeout(existingTimer);
          timers.delete(item);
        }
        item.classList.add('fbsa-submenu-hold');
        item.classList.add('opensub');
      }

      function closeHoldSoon() {
        const existingTimer = timers.get(item);
        if (existingTimer) {
          window.clearTimeout(existingTimer);
        }
        const timer = window.setTimeout(function () {
          item.classList.remove('fbsa-submenu-hold');
          if (!item.matches(':hover') && !submenu.matches(':hover')) {
            item.classList.remove('opensub');
          }
          timers.delete(item);
        }, 260);
        timers.set(item, timer);
      }

      item.addEventListener('mouseenter', openHold);
      item.addEventListener('mouseleave', closeHoldSoon);
      submenu.addEventListener('mouseenter', openHold);
      submenu.addEventListener('mouseleave', closeHoldSoon);
      item.addEventListener('focusin', openHold);
      item.addEventListener('focusout', closeHoldSoon);
    });
  }

  function initDashboardWebsiteStepsWidget() {
    const dashboardWidgets = Array.from(document.querySelectorAll('[data-fbsa-dashboard-checklist]'));
    if (!dashboardWidgets.length) return;

    dashboardWidgets.forEach(function (dashboardWidget) {
      const message = dashboardWidget.querySelector('.fbsa-dashboard-steps__message');
      const links = Array.from(dashboardWidget.querySelectorAll('[data-fbsa-placeholder-guide]'));

      links.forEach(function (link) {
        link.addEventListener('click', function (event) {
          event.preventDefault();
          const guideLabel = link.getAttribute('data-fbsa-placeholder-guide') || 'This guide';
          const videoUrl = link.getAttribute('data-fbsa-guide-video-url') || '';
          const videoLanguage = link.getAttribute('data-fbsa-guide-video-language') || '';
          const isFallback = link.getAttribute('data-fbsa-guide-video-fallback') === '1';

          if (videoUrl) {
            const videoWindow = window.open(videoUrl, '_blank', 'noopener,noreferrer');
            if (videoWindow) videoWindow.opener = null;
            if (message) {
              message.textContent = isFallback
                ? translateText('Opening the ' + videoLanguage + ' fallback video for ' + guideLabel + '.')
                : translateText('Opening the ' + videoLanguage + ' video for ' + guideLabel + '.');
              message.hidden = false;
            }
            return;
          }

          if (!message) return;
          message.textContent = translateText(guideLabel + ' guide will be available soon.');
          message.hidden = false;
        });
      });
    });
  }

  function init() {
    initSettingsTabs();
    initWelcomeBannerUploader();
    initDashboardWelcomeScreenOption();
    initDashboardWebsiteStepsWidget();
    initSettingsCustomCommandPanel();
    initConfirmableActions();
    initExpandableGuideVideo();
    initAdminSidebarHoverHold();

    const widget = $('#fbsa-floating-widget');
    if (!widget) return;

    initWidgetTheme(widget);
    applyFloatingWidgetViewportSizing();
    initFloatingWidgetDrag(widget);
    renderCategories();
    const autoGuideCommand = applyCurrentAdminPageGuide();
    syncSelectValues();
    renderSubcategories();
    renderCommands();
    syncSelectValues();
    renderCommandAction(autoGuideCommand || null);
    if (workflowLoadError && !(workflow.categories || []).length) {
      setMessage(workflowLoadError, 'error');
    }
    if (!autoGuideCommand) {
      updateVideo(null);
    }
    loadStatus();
    restoreExpandedVideoPanel();

    const categorySelect = $('#fbsa-category-select');
    const subcategorySelect = $('#fbsa-subcategory-select');
    const commandSelect = $('#fbsa-command-select');
    const actionButton = $('#fbsa-action-button');
    const panelToggleButtons = Array.from(document.querySelectorAll('#fbsa-floating-widget .fbsa-widget__toggle'));

    function setWidgetCollapsed(isCollapsed, shouldPersist) {
      widget.classList.add('fbsa-widget--animating');
      widget.classList.toggle('fbsa-widget--collapsed', isCollapsed);
      widget.setAttribute('data-fbsa-collapsed', isCollapsed ? 'true' : 'false');

      panelToggleButtons.forEach(function (button) {
        button.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
        button.textContent = isCollapsed ? '+' : '−';
        button.setAttribute('aria-label', translateText(isCollapsed ? 'Open FB Software AI popup' : 'Close FB Software AI popup'));
      });

      window.setTimeout(function () {
        widget.classList.remove('fbsa-widget--animating');
      }, 620);

      window.requestAnimationFrame(function () {
        applyFloatingWidgetViewportSizing();
      });

      if (shouldPersist !== false) {
        persistWidgetCollapsedState(isCollapsed);
      }
    }


    const initialCollapsedState = getStoredWidgetCollapsedState();
    const startCollapsed = initialCollapsedState === null ? widget.classList.contains('fbsa-widget--collapsed') : initialCollapsedState;
    setWidgetCollapsed(startCollapsed, false);
    widget.classList.remove('fbsa-widget--preinit');

    if (categorySelect) {
      categorySelect.addEventListener('change', function () {
        state.selectedCategory = this.value;
        state.selectedSubcategory = '';
        state.selectedCommand = null;
        renderSubcategories();
        renderCommands();
        renderCommandAction(null);
      });
    }

    if (subcategorySelect) {
      subcategorySelect.addEventListener('change', function () {
        state.selectedSubcategory = this.value;
        state.selectedCommand = null;
        renderCommands();
        renderCommandAction(null);
      });
    }

    if (commandSelect) {
      commandSelect.addEventListener('change', function () {
        const command = findCommand(this.value);
        renderCommandAction(command);
      });
    }

    if (actionButton) {
      actionButton.addEventListener('click', runSelectedCommand);
    }

    if (panelToggleButtons.length) {
      panelToggleButtons.forEach(function (button) {
        button.addEventListener('click', function () {
          setWidgetCollapsed(!widget.classList.contains('fbsa-widget--collapsed'));
        });
      });
    }

  }

  ready(init);
})();
