from __future__ import annotations
import hashlib, json, re, sys
from pathlib import Path

work = Path(__file__).resolve().parents[2]
baseline = work / 'tests' / 'fixtures' / 'v0.1.138' / 'fb-software-ai'
candidate = work
failures=[]
checks=0

def check(condition, message):
    global checks
    checks += 1
    print(('PASS: ' if condition else 'FAIL: ') + message)
    if not condition: failures.append(message)

# Existing main-file hook inventory must remain intact.
def hooks(text):
    rows=[]
    for m in re.finditer(r"add_(action|filter)\(\s*'([^']+)'\s*,\s*array\(\$this,\s*'([^']+)'\)(?:\s*,\s*([0-9]+))?", text):
        rows.append((m.group(1),m.group(2),m.group(3),int(m.group(4) or 10)))
    for m in re.finditer(r"add_action\(\s*'([^']+)'\s*,\s*'([^']+)'", text):
        rows.append(('action',m.group(1),m.group(2),10))
    return rows

base_php=(baseline/'fb-software-ai.php').read_text(encoding='utf-8')
cand_php=(candidate/'fb-software-ai.php').read_text(encoding='utf-8')
base_hooks=hooks(base_php)
cand_hooks=hooks(cand_php)
check(base_hooks == cand_hooks, 'All legacy main-file hook/filter declarations are preserved exactly from v0.1.138.')
check(len(cand_hooks)==21, 'Legacy main-file hook/filter declaration count remains 21.')

base_ajax=sorted(h[1] for h in base_hooks if h[1].startswith('wp_ajax_'))
cand_ajax=sorted(h[1] for h in cand_hooks if h[1].startswith('wp_ajax_'))
check(base_ajax==cand_ajax and len(cand_ajax)==3, 'All three legacy AJAX action declarations are preserved.')
check("do_action('fbsa_settings_tabs')" in cand_php, 'The legacy settings page exposes the Workspace tab extension point.')
check("do_action('fbsa_settings_panels')" in cand_php, 'The legacy settings page exposes the Workspace panel extension point.')

# Workflow IDs and command records are preserved; only top-level version changes.
def workflow_records(path):
    data=json.loads(path.read_text(encoding='utf-8'))
    rows=[]
    for cat in data.get('categories',[]):
        for cmd in cat.get('commands',[]) or []:
            rows.append((cat.get('id',''),'',cmd))
        for sub in cat.get('subcategories',[]) or []:
            for cmd in sub.get('commands',[]) or []:
                rows.append((cat.get('id',''),sub.get('id',''),cmd))
    return data,rows
base_wf,base_rows=workflow_records(baseline/'workflows/workflows.json')
cand_wf,cand_rows=workflow_records(candidate/'workflows/workflows.json')
check(len(cand_rows)==63, 'Workflow still contains 63 commands.')
check(base_rows==cand_rows, 'All workflow command records and IDs are unchanged from v0.1.138.')
check(cand_wf.get('version')=='0.1.139', 'Workflow package version is 0.1.139.')

# Existing UI assets remain byte-identical; new Workspace assets are isolated.
for rel in [
    'assets/admin.js','assets/css/admin-menu.css','assets/css/dashboard-welcome.css',
    'assets/css/settings.css','assets/css/video-player.css','assets/css/widget.css',
    'assets/fb-software-solutions-logo.svg','includes/i18n-catalog.php',
    'languages/README.txt'
]:
    a=(baseline/rel).read_bytes(); b=(candidate/rel).read_bytes()
    check(hashlib.sha256(a).digest()==hashlib.sha256(b).digest(), f'{rel} is byte-identical to v0.1.138.')
for rel in ['assets/js/workspace-settings.js','assets/css/workspace-settings.css']:
    check((candidate/rel).is_file() and (candidate/rel).stat().st_size > 0, f'{rel} exists as an isolated Workspace Controls asset.')
check('Project-Id-Version: FB Software AI 0.1.139' in (candidate/'languages/fb-software-ai.pot').read_text(encoding='utf-8'), 'Translation template version is updated to 0.1.139.')
check('Dashboard Widget Controls' in (candidate/'languages/fb-software-ai.pot').read_text(encoding='utf-8'), 'New Workspace controls strings are included in the translation template.')

# Version synchronization.
header=re.search(r'^\s*\*\s*Version:\s*(\S+)',cand_php,re.M).group(1)
const=re.search(r"const VERSION = '([^']+)'",cand_php).group(1)
readme=re.search(r'^Stable tag:\s*(\S+)',(candidate/'readme.txt').read_text(),re.M).group(1)
version_class=re.search(r"const PLUGIN = '([^']+)'",(candidate/'src/Core/Version.php').read_text()).group(1)
check(len({header,const,readme,cand_wf.get('version'),version_class})==1 and header=='0.1.139', 'Header, facade, readme, workflow, and architecture versions are synchronized.')
check("const WORKSPACE_SCHEMA = 2;" in (candidate/'src/Core/Version.php').read_text(), 'Workspace schema target is version 2.')

# Existing browser storage keys remain in unchanged JS.
js=(candidate/'assets/admin.js').read_text(encoding='utf-8')
for key in ['fbsaWidgetTheme','fbsaWidgetPosition','fbsaWidgetCollapsed','fbsaExpandedVideoPosition','fbsaExpandedVideoSize','fbsaGuideVideoPanelSession']:
    check(key in js, f'Browser storage key {key} remains present.')

# Workspace controls contract and preservation.
required_files = [
    'src/Workspace/WidgetDefinition.php',
    'src/Workspace/WidgetRegistryInterface.php',
    'src/Workspace/WidgetRegistry.php',
    'src/Workspace/WorkspaceModule.php',
    'src/Workspace/UserWorkspaceLayoutRepository.php',
    'src/Workspace/WorkspaceControls.php',
    'src/Workspace/WorkspaceRestController.php',
    'src/Workspace/WorkspaceSettingsRenderer.php',
    'src/Workspace/DashboardPreferenceSynchronizer.php',
    'src/Migrations/Workspace/Version0002WorkspaceFoundation.php',
    'src/Migrations/Workspace/Version0003WorkspaceControls.php',
]
for rel in required_files:
    check((candidate/rel).is_file(), f'{rel} exists in the Workspace Controls release.')

adapter=(candidate/'src/Workspace/LegacyDashboardWidgetAdapter.php').read_text()
for widget_id in [
    'fbsa_website_steps_widget','fbsa_plugin_setup_widget',
    'fbsa_website_settings_widget','fbsa_help_tutorials_widget'
]:
    check(widget_id in cand_php and widget_id in adapter, f'Widget ID {widget_id} is preserved in facade and registry adapter.')

module=(candidate/'src/Workspace/WorkspaceModule.php').read_text()
rest=(candidate/'src/Workspace/WorkspaceRestController.php').read_text()
controls=(candidate/'src/Workspace/WorkspaceControls.php').read_text()
sync=(candidate/'src/Workspace/DashboardPreferenceSynchronizer.php').read_text()
check("add_action('plugins_loaded', array($this, 'register_extensions'), 20)" in module, 'Local Widget SDK registration hook remains at plugins_loaded priority 20.')
check("add_action('rest_api_init'" in module, 'Workspace REST controller is registered on rest_api_init.')
check("fbsa_workspace_manage_capability" in (candidate/'src/Workspace/WorkspaceAccessPolicy.php').read_text(), 'Workspace management capability filter is present.')
check("wp_verify_nonce" in rest and "wp_rest" in rest, 'Workspace REST API explicitly verifies the WordPress REST nonce.')
check("unknown or unavailable widget" in controls, 'Workspace Controls explicitly reject unknown widget IDs.')
check("meta-box-order_dashboard" in sync, 'Native Dashboard order synchronization is isolated to the standard user option.')
check("Do not create a native Dashboard order option" in sync, 'Users relying on WordPress default ordering are not forced into a new native order option.')
check("fbsa_workspace_layout_loaded" in (candidate/'src/Workspace/UserWorkspaceLayoutRepository.php').read_text(), 'Workspace layout loaded filter remains present.')
check("fbsa_workspace_layout_saved" in (candidate/'src/Workspace/UserWorkspaceLayoutRepository.php').read_text(), 'Workspace layout saved action remains present.')
check("fbsa_widget_render_before" in cand_php and "fbsa_widget_render_after" in cand_php, 'Widget render lifecycle hooks wrap preserved renderers.')

print(f"\nChecks: {checks}; Failures: {len(failures)}")
sys.exit(1 if failures else 0)
