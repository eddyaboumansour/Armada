<?php
if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}
require_once QA_INCLUDE_DIR.'qa-theme-base.php';
require_once QA_INCLUDE_DIR.'qa-app-blobs.php';
require_once QA_PLUGIN_DIR.'q2a-extra-answer-field/qa-eaf.php';

class qa_html_theme_layer extends qa_html_theme_base {

	private $extradata;
	private $pluginurl;
	
	function doctype() {
		qa_html_theme_base::doctype();
		$this->pluginurl = qa_opt('site_url').'qa-plugin/q2a-extra-answer-field/';
		if($this->template == 'question') {
			if(isset($this->content['q_view']['raw']['postid']))
				$this->extradata = $this->qa_eaf_get_extradata($this->content['q_view']['raw']['postid']);
		}
	}
	function head_script() {
		qa_html_theme_base::head_script();
		if(count((array)$this->extradata) && $this->qa_eaf_file_exist() && qa_opt(qa_eaf::lightbox_effect)) {
			$this->output('<script type="text/javascript" src="'.$this->pluginurl.'magnific-popup/jquery.magnific-popup.min.js"></script>');
			$this->output('<script type="text/javascript">');
			$this->output('$(function(){');
			$this->output('	$(".qa-q-view-extra-upper-img, .qa-q-view-extra-inside-img, .qa-q-view-extra-img").magnificpopup({');
			$this->output('		type: \'image\',');
			$this->output('		terror: \'<a href="%url%">the image</a> could not be loaded.\',');
			$this->output('		image: {');
			$this->output('			titlesrc: \'title\'');
			$this->output('		},');
			$this->output('		gallery: {');
			$this->output('			enabled: true');
			$this->output('		},');
			$this->output('		callbacks: {');
			$this->output('			elementparse: function(item) {');
			$this->output('				console.log(item);');
			$this->output('			}');
			$this->output('		}');
			$this->output('	});');
			$this->output('});');
			$this->output('</script>');
		}
	}
	function head_css() {
		qa_html_theme_base::head_css();
		if(count((array)$this->extradata) && $this->qa_eaf_file_exist() && qa_opt(qa_eaf::lightbox_effect)) {
			$this->output('<link rel="stylesheet" type="text/css" href="'.$this->pluginurl.'magnific-popup/magnific-popup.css"/>');
		}
	}
	function main() {
		if($this->template == 'a_form') {
			if(isset($this->content['form']['fields']))
				$this->qa_eaf_add_field(null, $this->content['form']['fields'], $this->content['form']);
		} else if(isset($this->content['form_q_edit']['fields'])) {
				$this->qa_eaf_add_field($this->content['q_view']['raw']['postid'], $this->content['form_q_edit']['fields'], $this->content['form_q_edit']);
		}
		qa_html_theme_base::main();
	}
	function q_view_content($q_view) {
		if(!isset($this->content['form_q_edit'])) {
			$this->qa_eaf_output($q_view, qa_eaf::field_page_pos_upper);
			$this->qa_eaf_output($q_view, qa_eaf::field_page_pos_inside);
			$this->qa_eaf_clearhook($q_view);
		}
		qa_html_theme_base::q_view_content($q_view);
	}
	function q_view_extra($q_view) {
		qa_html_theme_base::q_view_extra($q_view);
		if(!isset($this->content['form_q_edit'])) {
			$this->qa_eaf_output($q_view, qa_eaf::field_page_pos_below);
		}
	}
	
	function qa_eaf_add_field($postid, &$fields, &$form) {
		global $qa_extra_answer_fields;
		$multipart = false;
		for($key=qa_eaf::field_count_max; $key>=1; $key--) {
			if((bool)qa_opt(qa_eaf::field_active.$key)) {
				$field = array();
				$name = qa_eaf::field_base_name.$key;
				$field['label'] = qa_opt(qa_eaf::field_prompt.$key);
				$type = qa_opt(qa_eaf::field_type.$key);
				switch ($type) {
				case qa_eaf::field_type_file:
					$field['type'] = 'custom';
					$value = qa_db_single_select(qa_db_post_meta_selectspec($postid, 'qa_a_'.$name));
					$original = '';
					if(!empty($value)) {
						$blob = qa_read_blob($value);
						$format = $blob['format'];
						$bloburl = qa_get_blob_url($value);
						$imageurl = str_replace('qa=blob', 'qa=image', $bloburl);
						$filename = $blob['filename'];
						$original = $filename;
						$width = $this->qa_eaf_get_image_width($blob['content']);
						if($width > qa_opt(qa_eaf::thumb_size))
							$width = qa_opt(qa_eaf::thumb_size);
						if($format == 'jpg' || $format == 'jpeg' || $format == 'png' || $format == 'gif')
							$original = '<img src="'.$imageurl.'&qa_size='.$width.'" alt="'.$filename.'" id="'.$name.'-thumb" class="'.qa_eaf::field_base_name.'-thumb"/>';
						$original = '<a href="'.$imageurl.'" target="_blank" id="'.$name.'-link" class="'.qa_eaf::field_base_name.'-link">' . $original . '</a>';
						$original .= '<input type="checkbox" name="'.$name.'-remove" id="'.$name.'-remove" class="'.qa_eaf::field_base_name.'-remove"/><label for="'.$name.'-remove">'.qa_lang(qa_eaf::plugin.'/eaf_remove').'</label><br>';
						$original .= '<input type="hidden" name="'.$name.'-old" id="'.$name.'-old" value="'.$value.'"/>';
					}
					$field['html'] = $original.'<input type="file" class="qa-form-tall-'.$type.'" name="'.$name.'">';
					$multipart = true;
					break;
				default:
					$field['type'] = qa_opt(qa_eaf::field_type.$key);
					$field['tags'] = 'name="'.$name.'"';
					$options = $this->qa_eaf_options(qa_opt(qa_eaf::field_option.$key));
					if (qa_opt(qa_eaf::field_attr.$key) != '')
						$field['tags'] .= ' '.qa_opt(qa_eaf::field_attr.$key);
					if ($field['type'] != qa_eaf::field_type_text && $field['type'] != qa_eaf::field_type_textarea)
						$field['options'] = $options;
					if(is_null($postid))
						$field['value'] = qa_opt(qa_eaf::field_default.$key);
					else
						$field['value'] = qa_db_single_select(qa_db_post_meta_selectspec($postid, 'qa_a_'.$name));
					if ($field['type'] != qa_eaf::field_type_text && $field['type'] != qa_eaf::field_type_textarea && is_array($field['options'])) {
						if($field['type'] == qa_eaf::field_type_check) {
							if($field['value'] == 0)
								$field['value'] = '';
						} else
							$field['value'] = @$field['options'][$field['value']];
					}
					if ($field['type'] == qa_eaf::field_type_textarea) {
						if(isset($options[0]))
							$field['rows'] = $options[0];
						if(empty($field['rows']))
							$field['rows'] = qa_eaf::field_option_rows_dfl;
					}
					break;
				}
				$field['note'] = nl2br(qa_opt(qa_eaf::field_note.$key));
				if(isset($qa_extra_answer_fields[$name]['error']))
					$field['error'] = $qa_extra_answer_fields[$name]['error'];
				$this->qa_eaf_insert_array($fields, $field, $name, qa_opt(qa_eaf::field_form_pos.$key));
			}
		}
		if($multipart) {
			$form['tags'] .= ' enctype="multipart/form-data"';
		}
	}
	function qa_eaf_insert_array(&$items, $insertitem, $insertkey, $findkey) {
		$newitems = array();
		if($findkey == qa_eaf::field_form_pos_top) {
			$newitems[$insertkey] = $insertitem;
			foreach($items as $key => $item)
				$newitems[$key] = $item;
		} elseif($findkey == qa_eaf::field_form_pos_bottom) {
			foreach($items as $key => $item)
				$newitems[$key] = $item;
			$newitems[$insertkey] = $insertitem;
		} else {
			if(!array_key_exists($findkey, $items))
				$findkey = qa_eaf::field_form_pos_dfl;
			foreach($items as $key => $item) {
				$newitems[$key] = $item;
				if($key == $findkey)
					$newitems[$insertkey] = $insertitem;
			}
		}
		$items = $newitems;
	}
	function qa_eaf_options($optionstr) {
		if(stripos($optionstr, '@eval') !== false)
			$optionstr = eval(str_ireplace('@eval', '', $optionstr));
		if(stripos($optionstr, '||') !== false)
			$items = explode('||',$optionstr);
		else
			$items = array($optionstr);
		$options = array();
		foreach($items as $item) {
			if(strstr($item,'==')) {
				$nameval = explode('==',$item);
				$options[$nameval[1]] = $nameval[0];
			} else
				$options[$item] = $item;
		}
		return $options;
	}
	function qa_eaf_output(&$q_view, $position) {
		$output = '';
		$isoutput = false;
		foreach($this->extradata as $key => $item) {
			if($item['position'] == $position) {
				$name = $item['name'];
				$type = $item['type'];
				$value = $item['value'];
				
				if ($type == qa_eaf::field_type_textarea)
					$value = nl2br($value);
				else if ($type == qa_eaf::field_type_check)
					if ($value == '')
						$value = 0;
				if ($type != qa_eaf::field_type_text && $type != qa_eaf::field_type_textarea && $type != qa_eaf::field_type_file) {
					$options = $this->qa_eaf_options(qa_opt(qa_eaf::field_option.$key));
					if(is_array($options))
						$value = @$options[$value];
				}
				
				if($value == '' && qa_opt(qa_eaf::field_hide_blank.$key))
					continue;
				
				switch ($position) {
				case qa_eaf::field_page_pos_upper:
					$outerclass = 'qa-a-view-extra-upper qa-a-view-extra-upper'.$key;
					$innertclass = 'qa-a-view-extra-upper-title qa-a-view-extra-upper-title'.$key;
					$innervclass = 'qa-a-view-extra-upper-content qa-a-view-extra-upper-content'.$key;
					$inneraclass = 'qa-a-view-extra-upper-link qa-a-view-extra-upper-link'.$key;
					$innericlass = 'qa-a-view-extra-upper-img qa-a-view-extra-upper-img'.$key;
					break;
				case qa_eaf::field_page_pos_inside:
					$outerclass = 'qa-a-view-extra-inside qa-a-view-extra-inside'.$key;
					$innertclass = 'qa-a-view-extra-inside-title qa-a-view-extra-inside-title'.$key;
					$innervclass = 'qa-a-view-extra-inside-content qa-a-view-extra-inside-content'.$key;
					$inneraclass = 'qa-a-view-extra-inside-link qa-a-view-extra-inside-link'.$key;
					$innericlass = 'qa-a-view-extra-inside-img qa-a-view-extra-inside-img'.$key;
					break;
				case qa_eaf::field_page_pos_below:
					$outerclass = 'qa-a-view-extra qa-a-view-extra'.$key;
					$innertclass = 'qa-a-view-extra-title qa-a-view-extra-title'.$key;
					$innervclass = 'qa-a-view-extra-content qa-a-view-extra-content'.$key;
					$inneraclass = 'qa-a-view-extra-link qa-a-view-extra-link'.$key;
					$innericlass = 'qa-a-view-extra-img qa-a-view-extra-img'.$key;
					break;
				}
				$title = qa_opt(qa_eaf::field_label.$key);
				if ($type == qa_eaf::field_type_file && $value != '') {
					if(qa_blob_exists($value)) {
						$blob = qa_read_blob($value);
						$format = $blob['format'];
						$bloburl = qa_get_blob_url($value);
						$imageurl = str_replace('qa=blob', 'qa=image', $bloburl);
						$filename = $blob['filename'];
						$width = $this->qa_eaf_get_image_width($blob['content']);
						if($width > qa_opt(qa_eaf::thumb_size))
							$width = qa_opt(qa_eaf::thumb_size);
						$value = $filename;
						if($format == 'jpg' || $format == 'jpeg' || $format == 'png' || $format == 'gif') {
							$value = '<img src="'.$imageurl.'&qa_size='.$width.'" alt="'.$filename.'" target="_blank"/>';
							$value = '<a href="'.$imageurl.'" class="'.$inneraclass.' '.$innericlass.'" title="'.$title.'">' . $value . '</a>';
						} else
							$value = '<a href="'.$bloburl.'" class="'.$inneraclass.'" title="'.$title.'">' . $value . '</a>';
					} else
						$value = '';
				}
				$output .= '<div class="'.$outerclass.'">';
				$output .= '<div class="'.$innertclass.'">'.$title.'</div>';
				$output .= '<div class="'.$innervclass.'">'.$value.'</div>';
				$output .= '</div>';
				
				if(qa_opt(qa_eaf::field_page_pos.$key) != qa_eaf::field_page_pos_inside)
					$this->output($output);
				else {
					if(isset($q_view['content'])) {
						$hook = str_replace('^', $key, qa_eaf::field_page_pos_hook);
						$q_view['content'] = str_replace($hook, $output, $q_view['content']);
					}
				}
				$isoutput = true;
			}
			$output = '';
		}
		if($isoutput)
			$this->output('<div style="clear:both;"></div>');
	}
	function qa_eaf_get_extradata($postid) {
		$extradata = array();
		for($key=1; $key<=qa_eaf::field_count_max; $key++) {
			if((bool)qa_opt(qa_eaf::field_active.$key) && (bool)qa_opt(qa_eaf::field_display.$key)) {
				$name = qa_eaf::field_base_name.$key;
				$value = qa_db_single_select(qa_db_post_meta_selectspec($postid, 'qa_a_'.$name));
				if($value == '' && qa_opt(qa_eaf::field_hide_blank.$key))
					continue;
				$extradata[$key] = array(
					'name'=>$name,
					'type'=>qa_opt(qa_eaf::field_type.$key),
					'position'=>qa_opt(qa_eaf::field_page_pos.$key),
					'value'=>$value,
				);
			}
		}
		return $extradata;
	}
	function qa_eaf_file_exist() {
		$fileexist = false;
		foreach($this->extradata as $key => $item) {
			if ($item['type'] == qa_eaf::field_type_file)
				$fileexist = true;
		}
		return $fileexist;
	}
	function qa_eaf_clearhook(&$q_view) {
		for($key=1; $key<=qa_eaf::field_count_max; $key++) {
			if(isset($q_view['content'])) {
				$hook = str_replace('^', $key, qa_eaf::field_page_pos_hook);
				$q_view['content'] = str_replace($hook, '', $q_view['content']);
			}
		}
	}
	function qa_eaf_get_image_width($content) {
		$image=@imagecreatefromstring($content);
		if (is_resource($image))
			return imagesx($image);
		else
			return null;
	}
}
/*
	Omit PHP closing tag to help avoid accidental output
*/