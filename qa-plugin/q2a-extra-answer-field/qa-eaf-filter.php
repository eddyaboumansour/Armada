<?php
if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}
require_once QA_INCLUDE_DIR.'qa-filter-basic.php';
require_once QA_INCLUDE_DIR.'qa-app-upload.php';
require_once QA_PLUGIN_DIR.'q2a-extra-answer-field/qa-eaf.php';

$qa_extra_answer_fields;


class qa_eaf_filter {
	function filter_answer(&$question, &$errors, $oldquestion) {
		global $qa_extra_answer_fields;
		$qa_extra_answer_fields = array();
		$fb = new qa_filter_basic();
		for($key=1; $key<=qa_eaf::field_count_max; $key++) {
			if(qa_opt(qa_eaf::field_active.$key)) {
				$name = qa_eaf::field_base_name.$key;
				$extradata = '';
				$checkvalue = '';
				if(qa_opt(qa_eaf::field_type.$key) != qa_eaf::field_type_file) {
					$extradata = qa_post_text($name);
					$checkvalue = $extradata;
				} else {
					$extradata = $this->file_info($name);
					if(!empty($extradata))
						$checkvalue = $extradata['name'];
					else {
						$oldextradata = qa_post_text($name.'-old');
						if(!empty($oldextradata))
							$checkvalue = $oldextradata;
					}
				}
				if(qa_opt(qa_eaf::field_required.$key)) {
					$fb->validate_length($errors, $name, $checkvalue, 1, 'qa_db_max_content_length');
					if(array_key_exists($name, $errors))
						$qa_extra_answer_fields[$name]['error'] = qa_lang_sub(qa_eaf::plugin.'/'.qa_eaf::field_required.'_message',qa_opt(qa_eaf::field_prompt.$key));
				}
				if(qa_opt(qa_eaf::field_type.$key) == qa_eaf::field_type_file) {
					if(!empty($extradata)) {
						$file_info = $this->file_info($name);
						if(is_array($file_info)) {
							$extstr = qa_opt(qa_eaf::field_option.$key);
							if(!empty($extstr)) {
								$exts = explode(',', $extstr);
								$names = explode('.', $file_info['name']);
								if(count($names)>=2) {
									$ext = $names[count($names)-1];
									if(!in_array($ext, $exts))
										$qa_extra_answer_fields[$name]['error'] = qa_lang_sub(qa_eaf::plugin.'/'.qa_eaf::field_option_ext_error, $extstr);
								} else
									$qa_extra_answer_fields[$name]['error'] = qa_lang_sub(qa_eaf::plugin.'/'.qa_eaf::field_option_ext_error, $extstr);
							}
							if(!isset($qa_extra_answer_fields[$name]['error'])) {
								$result = qa_upload_file(
									$file_info['tmp_name'],
									$file_info['name'],
									qa_opt(qa_eaf::maxfile_size),
									qa_opt(qa_eaf::only_image),
									qa_opt(qa_eaf::image_maxwidth),
									qa_opt(qa_eaf::image_maxheight)
									);
								if(isset($result['error']))
									$qa_extra_answer_fields[$name]['error'] = $result['error'];
								else
									$extradata = $result['blobid'];
							}
						}
					} else {
						$oldextradata = qa_post_text($name.'-old');
						if(!empty($oldextradata)) {
							if(qa_post_text($name.'-remove'))
								$extradata = '';
							else
								$extradata = $oldextradata;
						}
					}
				}
				if(isset($qa_extra_answer_fields[$name]['error']))
					$errors[$name] = $qa_extra_answer_fields[$name]['error'];
				else
					$qa_extra_answer_fields[$name]['value'] = $extradata;
			}
		}
	}
	function file_info($name) {
		if(array_key_exists($name, $_FILES) && $_FILES[$name]['name'] != '')
			return $_FILES[$name];
		else
			return '';
	}
}
/*
	Omit PHP closing tag to help avoid accidental output
*/