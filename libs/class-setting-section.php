<?php
require_once dirname(__FILE__) . '/class-form-component.php';

class WpOgp_SettingSection
{
	var $_page;
	var $_id;
	var $_title;
	var $_callback;
	var $_description = '';
	var $_fields = array();

	function WpOgp_SettingSection($page, $id = null, $title = null, $callback = '')
	{
		if (!$id) {
			$this->_id = 'default';
			$this->_title = null;

		} else {
			$this->_id = $id;
			$this->_title = empty($title) ? $this->_id : $title;
		}

		if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $this->_id)) {
			trigger_error(sprintf('Invalid section slug format: %s', $this->_id), E_USER_WARNING);
			return;
		}

		$this->_page = $page;

		if (!is_callable($callback)) {
			$this->_description = is_string($callback) ? $callback : '';
			$this->_callback = array(&$this, 'renderDescription');

		} else {
			$this->_callback = $callback;
		}

		add_action('admin_menu', array(&$this, 'register'));
	}

	function &createField($id, $title = null, $type = null, $options = array())
	{
		$this->_fields[$id] =& new WpOgp_SettingField($this->_page, $this->_id, $id, $title);

		if ($type) {
			$this->_fields[$id]->createForm($id, $type, $options);
		}

		return $this->_fields[$id];
	}

	function register()
	{
		if (!function_exists('add_settings_section')) {
			return false;
		}

		if ($this->_id !== 'default') {
			add_settings_section($this->_id, $this->_title, $this->_callback, $this->_page);
		}

		return true;
	}

	function renderDescription()
	{
		if ($this->_description) {
			echo "<p>{$this->_description}</p>\n";
		}
	}
}

class WpOgp_SettingField
{
	var $_page;
	var $_section;
	var $_id;
	var $_title;
	var $_forms = array();
	var $_registerForms = array();

	function WpOgp_SettingField($page, $section, $id, $title = null)
	{
		$this->_page = $page;
		$this->_section = $section;
		$this->_id = $id;
		$this->_title = empty($title) ? $this->_id : $title;

		add_action('admin_menu', array(&$this, 'register'));
	}

	function createForm($name = null, $type = null, $options = array())
	{
		if (!empty($options['name'])) {
			$name = $options['name'];
			unset($options['name']);

		} else if ($name) {
			if ($this->_section == 'default') {
				$name = $this->_page . '_' . $name;

			} else {
				$name = $this->_page . '_' . $this->_section . '_' . $name;
			}

		} else if (!$name && empty($this->_form)) {
			if ($this->_section == 'default') {
				$name = $this->_page;

			} else {
				$name = $this->_page . '_' . $this->_section;
			}
		}

		if ($name) {
			$this->_forms[$name] = $options + array('type' => $type, 'note' => '');
			$this->registerForm($name);
		}
	}

	function createText($text)
	{
		$html = '<p>' . $text . '</p>';
		$this->createHtml($html);
	}

	function createHtml($html)
	{
		$this->_forms[] = $html;
	}

	function registerForm($name)
	{
		$this->_registerForms[$name] = true;
	}

	function register()
	{
		if (!function_exists('add_settings_field')) {
			return false;
		}

		add_settings_field($this->_id, $this->_title, array(&$this, 'render'), $this->_page, $this->_section);

		foreach (array_keys($this->_registerForms) as $form) {
			register_setting($this->_page, $form);
		}
	}

	function render()
	{
		$out = array();

		foreach ($this->_forms as $name => $form) {
			if (is_array($form) && isset($form['type'])) {
				list($type, $note) = array($form['type'], $form['note']);
				unset($form['type'], $form['note']);

				if (!$type) {
					$type = 'text';
				}

				$value = get_option($name);

				if ($value !== false) {
					$form['value'] = $value;
				}

				$out[] = WpOgp_FormComponent::create($name, $type, $form);

				if ($note) {
					$out[] = "<span class=\"description\">{$note}</span>";
				}

			} else if (is_string($form)) {
				$out[] =  $form;
			}
		}

		echo implode("\n", $out);
	}
}