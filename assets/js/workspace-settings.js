(() => {
  'use strict';

  const config = window.fbsaWorkspaceControls || {};
  const i18n = config.i18n || {};

  const message = (key, fallback) => (typeof i18n[key] === 'string' && i18n[key] ? i18n[key] : fallback);

  const formatPosition = (columnLabel, number) => {
    const template = message('positionInColumn', '%1$s · Position %2$d');
    return template.replace('%1$s', String(columnLabel)).replace('%2$d', String(number));
  };

  const parseError = async (response) => {
    try {
      const payload = await response.json();
      if (payload && typeof payload.message === 'string' && payload.message) {
        return payload.message;
      }
    } catch (error) {
      // Use the generic message below when the response is not JSON.
    }
    return message('error', 'The Workspace could not be updated. Please try again.');
  };

  const request = async (method, body) => {
    const response = await fetch(config.endpoint, {
      method,
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': config.nonce || '',
      },
      body: body ? JSON.stringify(body) : undefined,
    });
    if (!response.ok) {
      throw new Error(await parseError(response));
    }
    return response.json();
  };

  document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-fbsa-workspace-controls]');
    if (!root || !config.endpoint) {
      return;
    }

    const list = root.querySelector('[data-fbsa-workspace-widget-list]');
    const saveButton = root.querySelector('[data-fbsa-workspace-save]');
    const resetButton = root.querySelector('[data-fbsa-workspace-reset]');
    const notice = root.querySelector('[data-fbsa-workspace-notice]');
    let dragged = null;
    if (!list || !saveButton || !resetButton || !notice) {
      return;
    }

    const cards = () => Array.from(list.querySelectorAll('[data-fbsa-workspace-widget]'));

    const setNotice = (text, type = 'success') => {
      notice.textContent = text;
      notice.hidden = false;
      notice.classList.remove('is-success', 'is-error', 'is-working');
      notice.classList.add(`is-${type}`);
    };

    const clearNotice = () => {
      notice.hidden = true;
      notice.textContent = '';
      notice.classList.remove('is-success', 'is-error', 'is-working');
    };

    const updatePositions = () => {
      cards().forEach((card) => {
        const column = card.getAttribute('data-widget-column') || '1';
        const columnCards = cards().filter((candidate) => (candidate.getAttribute('data-widget-column') || '1') === column);
        const index = columnCards.indexOf(card);
        const position = card.querySelector('[data-fbsa-workspace-position]');
        const up = card.querySelector('[data-fbsa-workspace-move="up"]');
        const down = card.querySelector('[data-fbsa-workspace-move="down"]');
        if (position) {
          position.textContent = formatPosition(position.getAttribute('data-column-label') || 'Dashboard column', index + 1);
        }
        if (up) {
          up.disabled = index === 0;
        }
        if (down) {
          down.disabled = index === columnCards.length - 1;
        }
      });
    };

    const serialize = () => ({
      context: 'dashboard',
      layoutId: 'fbsa-user-dashboard-v1',
      widgets: cards().map((card, index) => ({
        id: card.getAttribute('data-widget-id'),
        visible: Boolean(card.querySelector('[data-fbsa-workspace-visible]')?.checked),
        column: Number.parseInt(card.getAttribute('data-widget-column') || '1', 10),
        order: index,
      })),
    });

    const renderState = (state) => {
      if (!state || !Array.isArray(state.widgets)) {
        return;
      }
      const byId = new Map(cards().map((card) => [card.getAttribute('data-widget-id'), card]));
      state.widgets.forEach((widget) => {
        const card = byId.get(widget.id);
        if (!card) {
          return;
        }
        const checkbox = card.querySelector('[data-fbsa-workspace-visible]');
        if (checkbox && !checkbox.disabled) {
          checkbox.checked = Boolean(widget.visible);
        }
        if (Number.isInteger(widget.column)) {
          card.setAttribute('data-widget-column', String(widget.column));
        }
        list.appendChild(card);
      });
      updatePositions();
    };

    const setBusy = (busy) => {
      if (saveButton) {
        saveButton.disabled = busy;
      }
      if (resetButton) {
        resetButton.disabled = busy;
      }
      root.setAttribute('aria-busy', busy ? 'true' : 'false');
    };

    list.addEventListener('click', (event) => {
      const button = event.target.closest('[data-fbsa-workspace-move]');
      if (!button) {
        return;
      }
      const card = button.closest('[data-fbsa-workspace-widget]');
      if (!card) {
        return;
      }
      const direction = button.getAttribute('data-fbsa-workspace-move');
      const column = card.getAttribute('data-widget-column') || '1';
      const columnCards = cards().filter((candidate) => (candidate.getAttribute('data-widget-column') || '1') === column);
      const index = columnCards.indexOf(card);
      if (direction === 'up' && index > 0) {
        list.insertBefore(card, columnCards[index - 1]);
      }
      if (direction === 'down' && index >= 0 && index < columnCards.length - 1) {
        list.insertBefore(columnCards[index + 1], card);
      }
      clearNotice();
      updatePositions();
      card.focus({ preventScroll: true });
    });

    list.addEventListener('change', (event) => {
      if (event.target.matches('[data-fbsa-workspace-visible]')) {
        clearNotice();
      }
    });

    list.addEventListener('dragstart', (event) => {
      const card = event.target.closest('[data-fbsa-workspace-widget]');
      if (!card) {
        return;
      }
      dragged = card;
      card.classList.add('is-dragging');
      if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', card.getAttribute('data-widget-id') || '');
      }
    });

    list.addEventListener('dragover', (event) => {
      if (!dragged) {
        return;
      }
      event.preventDefault();
      const target = event.target.closest('[data-fbsa-workspace-widget]');
      if (!target || target === dragged || target.getAttribute('data-widget-column') !== dragged.getAttribute('data-widget-column')) {
        return;
      }
      const rectangle = target.getBoundingClientRect();
      const insertAfter = event.clientY > rectangle.top + rectangle.height / 2;
      list.insertBefore(dragged, insertAfter ? target.nextElementSibling : target);
    });

    list.addEventListener('dragend', () => {
      if (dragged) {
        dragged.classList.remove('is-dragging');
      }
      dragged = null;
      clearNotice();
      updatePositions();
    });

    saveButton?.addEventListener('click', async () => {
      setBusy(true);
      setNotice(message('saving', 'Saving Workspace…'), 'working');
      try {
        const state = await request('PUT', serialize());
        renderState(state);
        setNotice(message('saved', 'Workspace saved. Refresh the Dashboard to see the preferred order.'), 'success');
      } catch (error) {
        setNotice(error.message || message('error', 'The Workspace could not be updated. Please try again.'), 'error');
      } finally {
        setBusy(false);
      }
    });

    resetButton?.addEventListener('click', async () => {
      if (!window.confirm(message('confirmReset', 'Reset your FB Software AI Workspace to the default layout?'))) {
        return;
      }
      setBusy(true);
      setNotice(message('resetting', 'Resetting Workspace…'), 'working');
      try {
        const state = await request('DELETE');
        renderState(state);
        setNotice(message('reset', 'Workspace reset to the default layout.'), 'success');
      } catch (error) {
        setNotice(error.message || message('error', 'The Workspace could not be updated. Please try again.'), 'error');
      } finally {
        setBusy(false);
      }
    });

    cards().forEach((card) => {
      card.setAttribute('tabindex', '0');
    });
    updatePositions();
  });
})();
