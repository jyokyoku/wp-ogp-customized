<?php
require_once dirname(__FILE__) . '/class-setting-section.php';

class WpOgp_SettingPage
{
	var $_parent;
	var $_id;
	var $_title;
	var $_menuId;
	var $_menuTitle;
	var $_capability;
	var $_callbacks = array();

	function WpOgp_SettingPage($parent, $id, $title, $menuTitle, $capability)
	{
		if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $id)) {
			trigger_error(sprintf('Invalid page slug format: %s', $id), E_USER_WARNING);
			return;
		}

		$parents = array(
			'management' => 'tools.php',
			'options'    => 'options-general.php',
			'theme'      => 'themes.php',
			'plugin'     => 'plugins.php',
			'users'      => 'users.php',
			'profile'    => 'profile.php',
			'dashboard'  => 'index.php',
			'posts'      => 'edit.php',
			'media'      => 'upload.php',
			'links'      => 'link-manager.php',
			'pages'      => 'edit.php?post_type=page',
			'comments'   => 'edit-comments.php'
		);

		if (is_string($parent) && isset($parents[$parent])) {
			$parent = $parents[$parent];
		}

		if (is_string($parent) || (is_array($parent) && count($parent) > 3)) {
			$this->_parent = $parent;
			$this->_id = $id;
			$this->_title = $title;
			$this->_menuTitle = $menuTitle;
			$this->_capability = $capability;

			add_action('admin_menu', array(&$this, 'register'));
		}
	}

	function &createSection($id = null, $title = null, $callback = null)
	{
		if (!$id) {
			$id = 'default';
			$title = null;

		} else {
			if (!$title) {
				$title = $id;
			}
		}

		$this->_sections[$id] =& new WpOgp_SettingSection($this->_id, $id, $title, $callback);

		return $this->_sections[$id];
	}

	function register()
	{
		if (!function_exists('add_menu_page') || !function_exists('add_submenu_page')) {
			return false;
		}

		if (is_array($this->_parent)) {
			call_user_func_array('add_menu_page', $this->_parent);
			$parent = array_splice($this->_parent, 3, 1);

		} else {
			$parent = $this->_parent;
		}

		add_submenu_page($parent, $this->_title, $this->_menuTitle, $this->_capability, $this->_id, array(&$this, 'renderPage'));
	}

	function renderPage()
	{
		global $wp_settings_fields;
?>
<div class="wrap">
<h2><?php echo esc_html($this->_title) ?></h2>
<form method="post" action="options.php">
<?php settings_fields($this->_id); ?>
<?php if (!empty($wp_settings_fields[$this->_id]['default'])): ?>
<table class="form-table">
<?php do_settings_fields($this->_id, 'default'); ?>
</table>
<?php endif ?>
<?php do_settings_sections($this->_id); ?>
<?php submit_button(); ?>
</form>
</div>
<?php
	}
}