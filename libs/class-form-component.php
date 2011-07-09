<?php
/**
 * Automatic generation HTML-Form component class
 *
 * Modified from CakePHP 1.3.8 FormHelper class
 *
 * @copyright Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @copyright Copyright 2011, Inspire-design, Inc. (modify)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @link      http://inspire-design.net
 * @since     CakePHP(tm) v 0.2.9
 * @license   MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class WpOgp_FormComponent
{
	var $_tags = array(
		'input' => '<input name="%s" %s/>',
		'textarea' => '<textarea name="%s" %s>%s</textarea>',
		'hidden' => '<input type="hidden" name="%s" %s/>',
		'checkbox' => '<input type="checkbox" name="%s" %s/>',
		'checkboxmultiple' => '<input type="checkbox" name="%s[]"%s />',
		'radio' => '<input type="radio" name="%s" id="%s" %s />%s',
		'selectstart' => '<select name="%s"%s>',
		'selectmultiplestart' => '<select name="%s[]"%s>',
		'selectempty' => '<option value=""%s>&nbsp;</option>',
		'selectoption' => '<option value="%s"%s>%s</option>',
		'selectend' => '</select>',
		'optiongroup' => '<optgroup label="%s"%s>',
		'optiongroupend' => '</optgroup>',
		'checkboxmultiplestart' => '',
		'checkboxmultipleend' => '',
		'password' => '<input type="password" name="%s" %s/>',
		'label' => '<label for="%s"%s>%s</label>',
		'fieldset' => '<fieldset%s>%s</fieldset>',
		'fieldsetstart' => '<fieldset><legend>%s</legend>',
		'fieldsetend' => '</fieldset>',
		'legend' => '<legend>%s</legend>',
		'tag' => '<%s%s>%s</%s>',
		'tagstart' => '<%s%s>',
		'tagend' => '</%s>',
	);

	function &getInstance()
	{
		static $instance;

		if (!$instance) {
			$instance = new WpOgp_FormComponent();
		}

		return $instance;
	}

	function create($name, $type, $attributes = array())
	{
		$self =& WpOgp_FormComponent::getInstance();
		$out = '';

		switch ($type) {
			case 'text':
			case 'textarea':
			case 'password':
			case 'checkbox':
				$out = $self->{$type}($name, $attributes);
				break;

			case 'select':
				$options = $self->_extractOption('options', $attributes, array());
				$selected = $self->_extractOption('selected', $attributes, '');
				$out = $self->{$type}($name, $options, $selected, $attributes);
				break;

			case 'radio':
				$options = $self->_extractOption('options', $attributes, array());
				$out = $self->{$type}($name, $options, $attributes);
				break;
		}

		return $out;
	}

	function text($name, $attributes = array())
	{
		$self =& WpOgp_FormComponent::getInstance();
		$attributes = $self->_initAttribute($name, $attributes);

		return sprintf(
			$self->_tags['input'],
			$name,
			$self->_parseAttributes(array('type' => 'text') + $attributes, null, null, ' ')
		);
	}

	function password($name, $attributes = array())
	{
		$self =& WpOgp_FormComponent::getInstance();
		$attributes = $self->_initAttribute($name, $attributes);

		return sprintf(
			$self->_tags['password'],
			$name,
			$self->_parseAttributes($attributes, null, null, ' ')
		);
	}

	function textarea($name, $attributes = array())
	{
		$self =& WpOgp_FormComponent::getInstance();
		$attributes = $self->_initAttribute($name, $attributes);
		$value = $self->_extractOption('value', $attributes, '');

		return sprintf(
			$self->_tags['textarea'],
			$name,
			$self->_parseAttributes($attributes, null, null, ' '),
			$value
		);
	}

	function select($name, $options = array(), $selected = null, $attributes = array())
	{
		$self =& WpOgp_FormComponent::getInstance();
		$attributes = $self->_initAttribute($name, $attributes);

		$select = array();
		$style = null;
		$tag = null;
		$attributes += array(
			'class' => null,
			'empty' => '',
			'showParents' => false
		);

		$showEmpty = $self->_extractOption('empty', $attributes);
		$showParents = $self->_extractOption('showParents', $attributes);
		$separator = $this->_extractOption('separator', $attributes);

		if (is_string($options)) {
			$options = array($options => $options);

		} elseif (!is_array($options)) {
			$options = array();
		}

		if (!isset($selected)) {
			$selected = $attributes['value'];
		}

		if (isset($attributes) && array_key_exists('multiple', $attributes)) {
			$style = ($attributes['multiple'] === 'checkbox') ? 'checkbox' : null;
			$template = ($style) ? 'checkboxmultiplestart' : 'selectmultiplestart';
			$separator = ($style) ? $separator : null;
			$tag = $self->_tags[$template];
			$hiddenAttributes = array(
				'value' => '',
				'id' => $attributes['id'] . ($style ? '' : '-hidden'),
			);
			$select[] = $self->hidden($name, $hiddenAttributes);

		} else {
			$tag = $self->_tags['selectstart'];
		}

		if (!empty($tag) || isset($template)) {
			$select[] = sprintf($tag, $name, $self->_parseAttributes($attributes));
		}

		$emptyMulti = (
			$showEmpty !== null && $showEmpty !== false && !(
				empty($showEmpty) && (isset($attributes) &&
				array_key_exists('multiple', $attributes))
			)
		);

		if ($emptyMulti) {
			$showEmpty = ($showEmpty === true) ? '' : $showEmpty;
			$options = array_reverse($options, true);
			$options[''] = $showEmpty;
			$options = array_reverse($options, true);
		}

		$select = array_merge($select, $self->_selectOptions(
			array_reverse($options, true),
			$selected,
			array(),
			$showParents,
			array('style' => $style, 'separator' => $separator, 'name' => $name, 'class' => $attributes['class'])
		));

		$template = ($style == 'checkbox') ? 'checkboxmultipleend' : 'selectend';
		$select[] = $self->_tags[$template];

		return implode("\n", $select);
	}

	function checkbox($name, $attributes = array())
	{
		$self =& WpOgp_FormComponent::getInstance();
		$attributes = $self->_initAttribute($name, $attributes);
		$attributes += array('hiddenField' => true);
		$out = '';

		if (empty($attributes['options'])) {
			$attributes['options'] = '1';

		} else if (is_array($attributes['options'])) {
			$attributes['options'] = reset($attributes['options']);
		}

		if (
			(!isset($attributes['checked']) && !empty($attributes['value']) && $attributes['value'] == $attributes['options'])
			|| !empty($attributes['checked'])
		) {
			$attributes['checked'] = 'checked';
		}

		$attributes['value'] = $attributes['options'];
		unset($attributes['options']);

		if ($self->_extractOption('hiddenField', $attributes, false)) {
			$hiddenOptions = array(
				'id' => $attributes['id'] . '-hidden',
				'value' => '0',
			);

			if (isset($attributes['disabled']) && $attributes['disabled'] == true) {
				$hiddenOptions['disabled'] = 'disabled';
			}

			$out = $self->hidden($name, $hiddenOptions);
		}

		return $out . sprintf($self->_tags['checkbox'], $name, $self->_parseAttributes($attributes, null, null, ' '));
	}

	function radio($name, $options = array(), $attributes = array())
	{
		$self =& WpOgp_FormComponent::getInstance();
		$attributes = $self->_initAttribute($name, $attributes);

		$label = $self->_extractOption('label', $attributes, true);
		$inbetween = $self->_extractOption('separator', $attributes);
		$value = $self->_extractOption('value', $attributes);
		$hiddenField = $self->_extractOption('hiddenField', $attributes, true);

		$out = array();

		foreach ($options as $optValue => $optTitle) {
			$optionsHere = array('value' => $optValue);

			if (!is_null($value) && $optValue == $value) {
				$optionsHere['checked'] = 'checked';
			}

			$parsedOptions = $self->_parseAttributes(array_merge($attributes, $optionsHere), null, null, ' ');
			$tagName = $attributes['id'] . '-' . $optValue;

			if ($label) {
				$optTitle =  sprintf($self->_tags['label'], $tagName, null, $optTitle);
			}

			$out[] = sprintf($self->_tags['radio'], $name, $tagName, $parsedOptions, $optTitle);
		}

		$hidden = null;

		if ($hiddenField) {
			if (!isset($value) || $value === '') {
				$hidden = $self->hidden($name, array(
					'id' => $attributes['id'] . '-hidden',
					'value' => '',
				));
			}
		}

		$out = $hidden . implode($inbetween, $out);

		return $out;
	}

	function hidden($name, $attributes = array())
	{
		$self =& WpOgp_FormComponent::getInstance();
		$attributes = $self->_initAttribute($name, $attributes);

		return sprintf(
			$self->_tags['hidden'],
			$name,
			$self->_parseAttributes($attributes, null, '', ' ')
		);
	}

	function label($name, $text, $attributes = array())
	{
		$self =& WpOgp_FormComponent::getInstance();

		if (is_string($attributes)) {
			$attributes = array('class' => $attributes);
		}

		$labelFor = $self->_extractOption('for', $attributes, str_replace('_', '-', $name));

		return sprintf(
			$self->_tags['label'],
			$labelFor,
			$self->_parseAttributes($attributes),
			$text
		);
	}

	function tag($name, $text = null, $attributes = array())
	{
		$self =& WpOgp_FormComponent::getInstance();

		if (!is_array($attributes)) {
			$attributes = array('class' => $attributes);
		}

		if ($text === null) {
			$tag = 'tagstart';

		} else {
			$tag = 'tag';
		}

		return sprintf($self->_tags[$tag], $name, $self->_parseAttributes($attributes, null, ' ', ''), $text, $name);
	}

	function _initAttribute($name, $attributes = array())
	{
		if (!isset($attributes['id']) || empty($attributes['id'])) {
			$attributes['id'] = $name;
		}

		if ($attributes['default'] && !isset($attributes['value'])) {
			$attributes['value'] = $attributes['default'];
		}

		unset($attributes['default']);

		return $attributes;
	}

	function _parseAttributes($options, $exclude = null, $insertBefore = ' ', $insertAfter = null)
	{
		if (is_array($options)) {
			if (!is_array($exclude)) {
				$exclude = array();
			}

			$keys = array_diff(array_keys($options), $exclude);
			$values = array_intersect_key(array_values($options), $keys);
			$attributes = array();

			foreach ($keys as $index => $key) {
				if ($values[$index] !== false && $values[$index] !== null) {
					$attributes[] = $this->_formatAttribute($key, $values[$index]);
				}
			}

			$out = implode(' ', $attributes);

		} else {
			$out = $options;
		}

		return $out ? $insertBefore . $out . $insertAfter : '';
	}

	function _formatAttribute($key, $value)
	{
		$attributeFormat = '%s="%s"';
		$minimizedAttributes = array(
			'compact', 'checked', 'declare', 'readonly', 'disabled', 'selected',
			'defer', 'ismap', 'nohref', 'noshade', 'nowrap', 'multiple', 'noresize'
		);

		if (is_array($value)) {
			$value = '';
		}

		if (in_array($key, $minimizedAttributes)) {
			if ($value === 1 || $value === true || $value === 'true' || $value === '1' || $value == $key) {
				$attribute = sprintf($attributeFormat, $key, $key);
			}

		} else {
			$attribute = sprintf($attributeFormat, $key, $value);
		}

		return $attribute;
	}

	function _extractOption($name, &$options, $default = null, $unset = true) {
		if (array_key_exists($name, $options)) {
			$out = $options[$name];
			unset($options[$name]);

			return $out;
		}

		return $default;
	}

	function _selectOptions($elements = array(), $selected = null, $parents = array(), $showParents = null, $attributes = array())
	{
		$self;
		$select = array();
		$attributes = array_merge(array('style' => null, 'class' => null, 'separator' => null), $attributes);
		$selectedIsEmpty = ($selected === '' || $selected === null);
		$selectedIsArray = is_array($selected);

		foreach ($elements as $name => $title) {
			$htmlOptions = array();

			if (is_array($title) && (!isset($title['name']) || !isset($title['value']))) {
				if (!empty($name)) {
					if ($attributes['style'] === 'checkbox') {
						$select[] = $this->_tags['fieldsetend'];

					} else {
						$select[] = $this->_tags['optiongroupend'];
					}

					$parents[] = $name;
				}

				$select = array_merge($select, $this->_selectOptions(
					$title, $selected, $parents, $showParents, $attributes
				));

				if (!empty($name)) {
					if ($attributes['style'] === 'checkbox') {
						$select[] = sprintf($this->_tags['fieldsetstart'], $name);

					} else {
						$select[] = sprintf($this->_tags['optiongroup'], $name, '');
					}
				}

				$name = null;

			} else if (is_array($title)) {
				$htmlOptions = $title;
				$name = $title['value'];
				$title = $title['name'];
				unset($htmlOptions['name'], $htmlOptions['value']);
			}

			if ($name !== null) {
				if (
					(!$selectedIsArray && !$selectedIsEmpty && (string)$selected == (string)$name) ||
					($selectedIsArray && in_array($name, $selected))
				) {
					if ($attributes['style'] === 'checkbox') {
						$htmlOptions['checked'] = true;

					} else {
						$htmlOptions['selected'] = 'selected';
					}
				}

				if ($showParents || (!in_array($title, $parents))) {
					if ($attributes['style'] === 'checkbox') {
						$htmlOptions['value'] = $name;

						$tagName = str_replace('_', '-', $attributes['name']) . '-' . $name;
						$htmlOptions['id'] = $tagName;
						$label = array('for' => $tagName);

						if (isset($htmlOptions['checked']) && $htmlOptions['checked'] === true) {
							$label['class'] = 'selected';
						}

						$name = $attributes['name'];

						if (empty($attributes['class'])) {
							$attributes['class'] = 'checkbox';
						}

						$label = $this->label(null, $title, $label);
						$item = sprintf($this->_tags['checkboxmultiple'], $name, $this->_parseAttributes($htmlOptions));

						$select[] = $this->tag('span', $item . $label, $attributes['class']) . (string)$attributes['separator'];

					} else {
						$select[] = sprintf(
							$this->_tags['selectoption'],
							$name, $this->_parseAttributes($htmlOptions), $title
						);
					}
				}
			}
		}

		return array_reverse($select, true);
	}
}