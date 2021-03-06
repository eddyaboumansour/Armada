<?php
/*
	Blog Post by Jackson Siro
	https://www.github.com/Jacksiro/Q2A-Blog-Post-Plugin

	Description: Blog Post Plugin Admin pages manager

*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../../');
	exit;
}

	require_once QA_INCLUDE_DIR . 'db/admin.php';
	require_once QA_INCLUDE_DIR . 'db/maxima.php';
	require_once QA_INCLUDE_DIR . 'db/selects.php';
	require_once QA_INCLUDE_DIR . 'app/options.php';
	require_once QA_INCLUDE_DIR . 'app/admin.php';
	require_once QA_INCLUDE_DIR . 'qa-theme-base.php';
	require_once QA_INCLUDE_DIR . 'qa-app-blobs.php';
	require_once QA_PLUGIN_DIR . 'q2a-extra-answer-field/qa-eaf.php';

	class qa_html_theme_layer extends qa_html_theme_base {
		var $plugin_directory;
		var $plugin_url;
		
		public function __construct($template, $content, $rooturl, $request)
		{
			global $qa_layers;
			$this->plugin_directory = $qa_layers['Extra Answer Field Admin']['directory'];
			$this->plugin_url = $qa_layers['Extra Answer Field Admin']['urltoroot'];
			qa_html_theme_base::qa_html_theme_base($template, $content, $rooturl, $request);
		}
		
		function doctype()
		{
			global $qa_request;
			$adminsection = strtolower(qa_request_part(1));
			$errors = array();
			$securityexpired = false;
			
			if (strtolower(qa_request_part(1)) == 'eaf_admin') {
				$this->template = $adminsection;
				$this->eaf_navigation($adminsection);
				$this->content['suggest_next']="";
				$this->content['error'] = $securityexpired ? qa_lang_html('admin/form_security_expired') : qa_admin_page_error();
				$this->content['title'] = qa_lang_html('admin/admin_title') . ' - ' . qa_lang(qa_eaf::lang.'/'.qa_eaf::plugin.'_nav');
				$this->content = $this->eaf_admin();
			}
			qa_html_theme_base::doctype();
		}
		
		function nav_list($eaf_navigation, $class, $level=null)
		{
			if($this->template=='admin') {
				if ($class == 'nav-sub') {
					$eaf_navigation['eaf_admin'] = array(
						'label' => qa_lang(qa_eaf::lang.'/'.qa_eaf::plugin.'_nav'),
						'url' => qa_path_html('admin/eaf_admin'),
					);
				}
				if ( $this->request == 'admin/eaf_admin' ) $eaf_navigation = array_merge(qa_admin_sub_navigation(), $eaf_navigation);
			}
			if(count($eaf_navigation) > 1 ) 
				qa_html_theme_base::nav_list($eaf_navigation, $class, $level=null);	
		}
		
		function eaf_navigation($request)
		{
			$this->content['navigation']['sub'] = qa_admin_sub_navigation();
			$this->content['navigation']['sub']['eaf_admin'] = array(
				'label' => qa_lang(qa_eaf::lang.'/'.qa_eaf::plugin.'_nav'),	
				'url' => qa_path_html('admin/eaf_admin'),  
				'selected' => ($request == 'eaf_admin' ) ? 'selected' : '',
			);
			return 	$this->content['navigation']['sub'];
		}
		
		function eaf_admin()
		{
			$eaf = new qa_eaf;
			$saved = '';
			$error = false;
			$error_active = array();
			$error_prompt = array();
			$error_note = array();
			$error_type = array();
			$error_attr = array();
			$error_option = array();
			$error_default = array();
			$error_form_pos = array();
			$error_display = array();
			$error_label = array();
			$error_page_pos = array();
			$error_hide_blank = array();
			$error_required = array();
			
			for($key=1; $key<=qa_eaf::field_count_max; $key++) {
				$error_active[$key] = $error_prompt[$key] = $error_note[$key] = $error_type[$key] = $error_attr[$key] = $error_option[$key] = $error_default[$key] = $error_form_pos[$key] = $error_display[$key] = $error_label[$key] = $error_page_pos[$key] = $error_hide_blank[$key] = $error_required[$key] = '';
			}
			if (qa_clicked(qa_eaf::save_button)) {
				qa_opt(qa_eaf::field_count, qa_post_text(qa_eaf::field_count.'_field'));
				qa_opt(qa_eaf::maxfile_size, qa_post_text(qa_eaf::maxfile_size.'_field'));
				qa_opt(qa_eaf::only_image, (int)qa_post_text(qa_eaf::only_image.'_field'));
				qa_opt(qa_eaf::image_maxwidth, qa_post_text(qa_eaf::image_maxwidth.'_field'));
				qa_opt(qa_eaf::image_maxheight, qa_post_text(qa_eaf::image_maxheight.'_field'));
				qa_opt(qa_eaf::thumb_size, qa_post_text(qa_eaf::thumb_size.'_field'));
				qa_opt(qa_eaf::lightbox_effect, (int)qa_post_text(qa_eaf::lightbox_effect.'_field'));
				$eaf->init_extra_fields(qa_post_text(qa_eaf::field_count.'_field'));
				foreach ($eaf->extra_fields as $key => $extra_field) {
					if (trim(qa_post_text(qa_eaf::field_prompt.'_field'.$key)) == '') {
						$error_prompt[$key] = qa_lang(qa_eaf::lang.'/'.qa_eaf::field_prompt.'_error');
						$error = true;
					}
					if (qa_post_text(qa_eaf::field_type.'_field'.$key) != qa_eaf::field_type_text
					&& qa_post_text(qa_eaf::field_type.'_field'.$key) != qa_eaf::field_type_textarea
					&& qa_post_text(qa_eaf::field_type.'_field'.$key) != qa_eaf::field_type_file
					&& trim(qa_post_text(qa_eaf::field_option.'_field'.$key)) == '') {
						$error_option[$key] = qa_lang(qa_eaf::lang.'/'.qa_eaf::field_option.'_error');
						$error = true;
					}
					/*
					if ((bool)qa_post_text(qa_eaf::field_display.'_field'.$key) && trim(qa_post_text(qa_eaf::field_label.'_field'.$key)) == '') {
						$error_label[$key] = qa_lang(qa_eaf::lang.'/'.qa_eaf::field_label.'_error');
						$error = true;
					}
					*/
				}
				foreach ($eaf->extra_fields as $key => $extra_field) {
					qa_opt(qa_eaf::field_active.$key, (int)qa_post_text(qa_eaf::field_active.'_field'.$key));
					qa_opt(qa_eaf::field_prompt.$key, qa_post_text(qa_eaf::field_prompt.'_field'.$key));
					qa_opt(qa_eaf::field_note.$key, qa_post_text(qa_eaf::field_note.'_field'.$key));
					qa_opt(qa_eaf::field_type.$key, qa_post_text(qa_eaf::field_type.'_field'.$key));
					qa_opt(qa_eaf::field_option.$key, qa_post_text(qa_eaf::field_option.'_field'.$key));
					qa_opt(qa_eaf::field_attr.$key, qa_post_text(qa_eaf::field_attr.'_field'.$key));
					qa_opt(qa_eaf::field_default.$key, qa_post_text(qa_eaf::field_default.'_field'.$key));
					qa_opt(qa_eaf::field_form_pos.$key, qa_post_text(qa_eaf::field_form_pos.'_field'.$key));
					qa_opt(qa_eaf::field_display.$key, (int)qa_post_text(qa_eaf::field_display.'_field'.$key));
					qa_opt(qa_eaf::field_label.$key, qa_post_text(qa_eaf::field_label.'_field'.$key));
					qa_opt(qa_eaf::field_page_pos.$key, qa_post_text(qa_eaf::field_page_pos.'_field'.$key));
					qa_opt(qa_eaf::field_hide_blank.$key, (int)qa_post_text(qa_eaf::field_hide_blank.'_field'.$key));
					qa_opt(qa_eaf::field_required.$key, (int)qa_post_text(qa_eaf::field_required.'_field'.$key));
				}
				$saved = qa_lang_html(qa_eaf::lang.'/'.qa_eaf::saved_message);
			}
			if (qa_clicked(qa_eaf::dfl_button)) {
				$eaf->init_extra_fields(qa_eaf::field_count_max);
				foreach ($eaf->extra_fields as $key => $extra_field) {
					qa_opt(qa_eaf::field_active.$key, (int)$extra_field['active']);
					qa_opt(qa_eaf::field_prompt.$key, $extra_field['prompt']);
					qa_opt(qa_eaf::field_note.$key, $extra_field['note']);
					qa_opt(qa_eaf::field_type.$key, $extra_field['type']);
					qa_opt(qa_eaf::field_option.$key, $extra_field['option']);
					qa_opt(qa_eaf::field_attr.$key, $extra_field['attr']);
					qa_opt(qa_eaf::field_default.$key, $extra_field['default']);
					qa_opt(qa_eaf::field_form_pos.$key, $extra_field['form_pos']);
					qa_opt(qa_eaf::field_display.$key, (int)$extra_field['display']);
					qa_opt(qa_eaf::field_label.$key, $extra_field['label']);
					qa_opt(qa_eaf::field_page_pos.$key, $extra_field['page_pos']);
					qa_opt(qa_eaf::field_hide_blank.$key, (int)$extra_field['displayblank']);
					qa_opt(qa_eaf::field_required.$key, (int)$extra_field['required']);
				}
				$eaf->eaf_count = qa_eaf::field_count_dfl;
				qa_opt(qa_eaf::field_count,$eaf->eaf_count);
				$eaf->init_extra_fields($eaf->eaf_count);
				$eaf->eaf_maxfile_size = qa_eaf::maxfile_size_dfl;
				$eaf->eaf_only_image = qa_eaf::only_image_dfl;
				$eaf->eaf_image_maxwidth = qa_eaf::image_maxwidth_dfl;
				$eaf->eaf_image_maxheight = qa_eaf::image_maxheight_dfl;
				$eaf->eaf_thumb_size = qa_eaf::thumb_size_dfl;
				$eaf->eaf_lightbox_effect = qa_eaf::lightbox_effect_dfl;
				qa_opt(qa_eaf::thumb_size,$eaf->eaf_thumb_size);
				$saved = qa_lang_html(qa_eaf::lang.'/'.qa_eaf::reset_message);
			}
			if ($saved == '' && !$error) {
				$eaf->eaf_count = qa_opt(qa_eaf::field_count);
				if(!is_numeric($eaf->eaf_count))
					$eaf->eaf_count = qa_eaf::field_count_dfl;
				$eaf->init_extra_fields($eaf->eaf_count);
				$eaf->eaf_maxfile_size = qa_opt(qa_eaf::maxfile_size);
				if(!is_numeric($eaf->eaf_maxfile_size))
					$eaf->eaf_maxfile_size = qa_eaf::maxfile_size_dfl;
				$eaf->eaf_image_maxwidth = qa_opt(qa_eaf::image_maxwidth);
				if(!is_numeric($eaf->eaf_image_maxwidth))
					$eaf->eaf_image_maxwidth = qa_eaf::image_maxwidth_dfl;
				$eaf->eaf_image_maxheight = qa_opt(qa_eaf::image_maxheight);
				if(!is_numeric($eaf->eaf_image_maxheight))
					$eaf->eaf_image_maxheight = qa_eaf::image_maxheight_dfl;
				$eaf->eaf_thumb_size = qa_opt(qa_eaf::thumb_size);
				if(!is_numeric($eaf->eaf_thumb_size))
					$eaf->eaf_thumb_size = qa_eaf::thumb_size_dfl;
			}
			$rules = array();
			foreach ($eaf->extra_fields as $key => $extra_field) {
				$rules[qa_eaf::field_prompt.$key] = qa_eaf::field_active.'_field'.$key;
				$rules[qa_eaf::field_note.$key] = qa_eaf::field_active.'_field'.$key;
				$rules[qa_eaf::field_type.$key] = qa_eaf::field_active.'_field'.$key;
				$rules[qa_eaf::field_option.$key] = qa_eaf::field_active.'_field'.$key;
				$rules[qa_eaf::field_attr.$key] = qa_eaf::field_active.'_field'.$key;
				$rules[qa_eaf::field_default.$key] = qa_eaf::field_active.'_field'.$key;
				$rules[qa_eaf::field_form_pos.$key] = qa_eaf::field_active.'_field'.$key;
				$rules[qa_eaf::field_display.$key] = qa_eaf::field_active.'_field'.$key;
				$rules[qa_eaf::field_label.$key] = qa_eaf::field_active.'_field'.$key;
				$rules[qa_eaf::field_page_pos.$key] = qa_eaf::field_active.'_field'.$key;
				$rules[qa_eaf::field_hide_blank.$key] = qa_eaf::field_active.'_field'.$key;
				$rules[qa_eaf::field_required.$key] = qa_eaf::field_active.'_field'.$key;
			}
			qa_set_display_rules($qa_content, $rules);

			$ret = array();
			if($saved != '' && !$error)
				$this->content['form']['ok'] = $saved;

			$fields = array();
			$fieldoption = array();
			for($i=qa_eaf::field_count_dfl;$i<=qa_eaf::field_count_max;$i++) {
				$fieldoption[(string)$i] = (string)$i;
			}
			$fields[] = array(
				'id' => qa_eaf::field_count,
				'label' => qa_lang_html(qa_eaf::lang.'/'.qa_eaf::field_count.'_label'),
				'value' => qa_opt(qa_eaf::field_count),
				'tags' => 'name="'.qa_eaf::field_count.'_field"',
				'type' => 'select',
				'options' => $fieldoption,
				'note' => qa_lang(qa_eaf::lang.'/'.qa_eaf::field_count.'_note'),
			);
			$fields[] = array(
				'id' => qa_eaf::maxfile_size,
				'label' => qa_lang_html(qa_eaf::lang.'/'.qa_eaf::maxfile_size.'_label'),
				'value' => qa_opt(qa_eaf::maxfile_size),
				'tags' => 'name="'.qa_eaf::maxfile_size.'_field"',
				'type' => 'number',
				'suffix' => 'bytes',
				'note' => qa_lang(qa_eaf::lang.'/'.qa_eaf::maxfile_size.'_note'),
			);
			$fields[] = array(
				'id' => qa_eaf::only_image,
				'label' => qa_lang_html(qa_eaf::lang.'/'.qa_eaf::only_image.'_label'),
				'type' => 'checkbox',
				'value' => (int)qa_opt(qa_eaf::only_image),
				'tags' => 'name="'.qa_eaf::only_image.'_field"',
			);
			$fields[] = array(
				'id' => qa_eaf::image_maxwidth,
				'label' => qa_lang_html(qa_eaf::lang.'/'.qa_eaf::image_maxwidth.'_label'),
				'value' => qa_opt(qa_eaf::image_maxwidth),
				'tags' => 'name="'.qa_eaf::image_maxwidth.'_field"',
				'type' => 'number',
				'suffix' => qa_lang_html('admin/pixels'),
				'note' => qa_lang(qa_eaf::lang.'/'.qa_eaf::image_maxwidth.'_note'),
			);
			$fields[] = array(
				'id' => qa_eaf::image_maxheight,
				'label' => qa_lang_html(qa_eaf::lang.'/'.qa_eaf::image_maxheight.'_label'),
				'value' => qa_opt(qa_eaf::image_maxheight),
				'tags' => 'name="'.qa_eaf::image_maxheight.'_field"',
				'type' => 'number',
				'suffix' => qa_lang_html('admin/pixels'),
				'note' => qa_lang(qa_eaf::lang.'/'.qa_eaf::image_maxheight.'_note'),
			);
			$fields[] = array(
				'id' => qa_eaf::thumb_size,
				'label' => qa_lang_html(qa_eaf::lang.'/'.qa_eaf::thumb_size.'_label'),
				'value' => qa_opt(qa_eaf::thumb_size),
				'tags' => 'name="'.qa_eaf::thumb_size.'_field"',
				'type' => 'number',
				'suffix' => qa_lang_html('admin/pixels'),
				'note' => qa_lang(qa_eaf::lang.'/'.qa_eaf::thumb_size.'_note'),
			);
			$fields[] = array(
				'id' => qa_eaf::lightbox_effect,
				'label' => qa_lang_html(qa_eaf::lang.'/'.qa_eaf::lightbox_effect.'_label'),
				'type' => 'checkbox',
				'value' => (int)qa_opt(qa_eaf::lightbox_effect),
				'tags' => 'name="'.qa_eaf::lightbox_effect.'_field"',
			);
			$type = array(qa_eaf::field_type_text => qa_lang_html(qa_eaf::lang.'/'.qa_eaf::field_type_text_label)
						, qa_eaf::field_type_textarea => qa_lang_html(qa_eaf::lang.'/'.qa_eaf::field_type_textarea_label)
						, qa_eaf::field_type_check => qa_lang_html(qa_eaf::lang.'/'.qa_eaf::field_type_check_label)
						, qa_eaf::field_type_select => qa_lang_html(qa_eaf::lang.'/'.qa_eaf::field_type_select_label)
						, qa_eaf::field_type_radio => qa_lang_html(qa_eaf::lang.'/'.qa_eaf::field_type_radio_label)
						, qa_eaf::field_type_file => qa_lang_html(qa_eaf::lang.'/'.qa_eaf::field_type_file_label)
			);

			$form_pos = array();
			$form_pos[qa_eaf::field_form_pos_top] = qa_lang_html(qa_eaf::lang.'/'.qa_eaf::field_form_pos_top_label);
			if(qa_opt('show_custom_ask'))
				$form_pos[qa_eaf::field_form_pos_custom] = qa_lang_html(qa_eaf::lang.'/'.qa_eaf::field_form_pos_custom_label);
			$form_pos[qa_eaf::field_form_pos_title] = qa_lang_html(qa_eaf::lang.'/'.qa_eaf::field_form_pos_title_label);
			if (qa_using_categories())
				$form_pos[qa_eaf::field_form_pos_category] = qa_lang_html(qa_eaf::lang.'/'.qa_eaf::field_form_pos_category_label);
			$form_pos[qa_eaf::field_form_pos_content] = qa_lang_html(qa_eaf::lang.'/'.qa_eaf::field_form_pos_content_label);
			if (qa_opt('eaf_active'))
				$form_pos[qa_eaf::field_form_pos_extra] = qa_lang_html(qa_eaf::lang.'/'.qa_eaf::field_form_pos_extra_label);
			if (qa_using_tags())
				$form_pos[qa_eaf::field_form_pos_tags] = qa_lang_html(qa_eaf::lang.'/'.qa_eaf::field_form_pos_tags_label);
			$form_pos[qa_eaf::field_form_pos_notify] = qa_lang_html(qa_eaf::lang.'/'.qa_eaf::field_form_pos_notify_label);
			$form_pos[qa_eaf::field_form_pos_bottom] = qa_lang_html(qa_eaf::lang.'/'.qa_eaf::field_form_pos_bottom_label);

			$page_pos = array(qa_eaf::field_page_pos_upper => qa_lang_html(qa_eaf::lang.'/'.qa_eaf::field_page_pos_upper_label)
							, qa_eaf::field_page_pos_inside => qa_lang_html(qa_eaf::lang.'/'.qa_eaf::field_page_pos_inside_label)
							, qa_eaf::field_page_pos_below => qa_lang_html(qa_eaf::lang.'/'.qa_eaf::field_page_pos_below_label)
			);
			
			foreach ($eaf->extra_fields as $key => $extra_field) {
				$fields[] = array(
					'id' => qa_eaf::field_active.$key,
					'label' => qa_lang_html_sub(qa_eaf::lang.'/'.qa_eaf::field_active.'_label',$key),
					'type' => 'checkbox',
					'value' => (int)qa_opt(qa_eaf::field_active.$key),
					'tags' => 'name="'.qa_eaf::field_active.'_field'.$key.'" id="'.qa_eaf::field_active.'_field'.$key.'"',
					'error' => $error_active[$key],
				);
				$fields[] = array(
					'id' => qa_eaf::field_prompt.$key,
					'label' => qa_lang_html_sub(qa_eaf::lang.'/'.qa_eaf::field_prompt.'_label',$key),
					'value' => qa_html(qa_opt(qa_eaf::field_prompt.$key)),
					'tags' => 'name="'.qa_eaf::field_prompt.'_field'.$key.'" id="'.qa_eaf::field_prompt.'_field'.$key.'"',
					'note' => qa_lang(qa_eaf::lang.'/'.qa_eaf::field_prompt.'_note',$key),
					'error' => $error_prompt[$key],
				);
				$fields[] = array(
					'id' => qa_eaf::field_note.$key,
					'label' => qa_lang_html_sub(qa_eaf::lang.'/'.qa_eaf::field_note.'_label',$key),
					'type' => 'textarea',
					'value' => qa_opt(qa_eaf::field_note.$key),
					'tags' => 'name="'.qa_eaf::field_note.'_field'.$key.'" id="'.qa_eaf::field_note.'_field'.$key.'"',
					'rows' => $eaf->eaf_note_height,
					'note' => qa_lang(qa_eaf::lang.'/'.qa_eaf::field_note.'_note',$key),
					'error' => $error_note[$key],
				);
				$fields[] = array(
					'id' => qa_eaf::field_type.$key,
					'label' => qa_lang_html_sub(qa_eaf::lang.'/'.qa_eaf::field_type.'_label',$key),
					'tags' => 'name="'.qa_eaf::field_type.'_field'.$key.'" id="'.qa_eaf::field_type.'_field'.$key.'"',
					'type' => 'select',
					'options' => $type,
					'value' => @$type[qa_opt(qa_eaf::field_type.$key)],
					'note' => qa_lang(qa_eaf::lang.'/'.qa_eaf::field_type.'_note',$key),
					'error' => $error_type[$key],
				);
				$fields[] = array(
					'id' => qa_eaf::field_option.$key,
					'label' => qa_lang_html_sub(qa_eaf::lang.'/'.qa_eaf::field_option.'_label',$key),
					'type' => 'textarea',
					'value' => qa_opt(qa_eaf::field_option.$key),
					'tags' => 'name="'.qa_eaf::field_option.'_field'.$key.'" id="'.qa_eaf::field_option.'_field'.$key.'"',
					'rows' => $eaf->eaf_option_height,
					'note' => qa_lang(qa_eaf::lang.'/'.qa_eaf::field_option.'_note',$key),
					'error' => $error_option[$key],
				);
				$fields[] = array(
					'id' => qa_eaf::field_attr.$key,
					'label' => qa_lang_html_sub(qa_eaf::lang.'/'.qa_eaf::field_attr.'_label',$key),
					'value' => qa_html(qa_opt(qa_eaf::field_attr.$key)),
					'tags' => 'name="'.qa_eaf::field_attr.'_field'.$key.'" id="'.qa_eaf::field_attr.'_field'.$key.'"',
					'note' => qa_lang(qa_eaf::lang.'/'.qa_eaf::field_attr.'_note',$key),
					'error' => $error_attr[$key],
				);
				$fields[] = array(
					'id' => qa_eaf::field_default.$key,
					'label' => qa_lang_html_sub(qa_eaf::lang.'/'.qa_eaf::field_default.'_label',$key),
					'value' => qa_html(qa_opt(qa_eaf::field_default.$key)),
					'tags' => 'name="'.qa_eaf::field_default.'_field'.$key.'" id="'.qa_eaf::field_default.'_field'.$key.'"',
					'note' => qa_lang(qa_eaf::lang.'/'.qa_eaf::field_default.'_note',$key),
					'error' => $error_default[$key],
				);
				$fields[] = array(
					'id' => qa_eaf::field_form_pos.$key,
					'label' => qa_lang_html_sub(qa_eaf::lang.'/'.qa_eaf::field_form_pos.'_label',$key),
					'tags' => 'name="'.qa_eaf::field_form_pos.'_field'.$key.'" id="'.qa_eaf::field_form_pos.'_field'.$key.'"',
					'type' => 'select',
					'options' => $form_pos,
					'value' => @$form_pos[qa_opt(qa_eaf::field_form_pos.$key)],
					'note' => qa_lang(qa_eaf::lang.'/'.qa_eaf::field_form_pos.'_note',$key),
					'error' => $error_form_pos[$key],
				);
				$fields[] = array(
					'id' => qa_eaf::field_display.$key,
					'label' => qa_lang_html_sub(qa_eaf::lang.'/'.qa_eaf::field_display.'_label',$key),
					'type' => 'checkbox',
					'value' => (int)qa_opt(qa_eaf::field_display.$key),
					'tags' => 'name="'.qa_eaf::field_display.'_field'.$key.'" id="'.qa_eaf::field_display.'_field'.$key.'"',
					'note' => qa_lang(qa_eaf::lang.'/'.qa_eaf::field_display.'_note',$key),
					'error' => $error_display[$key],
				);
				$fields[] = array(
					'id' => qa_eaf::field_label.$key,
					'label' => qa_lang_html_sub(qa_eaf::lang.'/'.qa_eaf::field_label.'_label',$key),
					'value' => qa_html(qa_opt(qa_eaf::field_label.$key)),
					'tags' => 'name="'.qa_eaf::field_label.'_field'.$key.'" id="'.qa_eaf::field_label.'_field'.$key.'"',
					'note' => qa_lang(qa_eaf::lang.'/'.qa_eaf::field_label.'_note',$key),
					'error' => $error_label[$key],
				);
				$fields[] = array(
					'id' => qa_eaf::field_page_pos.$key,
					'label' => qa_lang_html_sub(qa_eaf::lang.'/'.qa_eaf::field_page_pos.'_label',$key),
					'tags' => 'name="'.qa_eaf::field_page_pos.'_field'.$key.'" id="'.qa_eaf::field_page_pos.'_field'.$key.'"',
					'type' => 'select',
					'options' => $page_pos,
					'value' => @$page_pos[qa_opt(qa_eaf::field_page_pos.$key)],
					'note' => qa_lang_html_sub(qa_eaf::lang.'/'.qa_eaf::field_page_pos.'_note',str_replace('^', $key, qa_eaf::field_page_pos_hook)),
					'error' => $error_page_pos[$key],
				);
				$fields[] = array(
					'id' => qa_eaf::field_hide_blank.$key,
					'label' => qa_lang_html_sub(qa_eaf::lang.'/'.qa_eaf::field_hide_blank.'_label',$key),
					'type' => 'checkbox',
					'value' => (int)qa_opt(qa_eaf::field_hide_blank.$key),
					'tags' => 'name="'.qa_eaf::field_hide_blank.'_field'.$key.'" id="'.qa_eaf::field_hide_blank.'_field'.$key.'"',
					'note' => qa_lang(qa_eaf::lang.'/'.qa_eaf::field_hide_blank.'_note',$key),
					'error' => $error_hide_blank[$key],
				);
				$fields[] = array(
					'id' => qa_eaf::field_required.$key,
					'label' => qa_lang_html_sub(qa_eaf::lang.'/'.qa_eaf::field_required.'_label',$key),
					'type' => 'checkbox',
					'value' => (int)qa_opt(qa_eaf::field_required.$key),
					'tags' => 'name="'.qa_eaf::field_required.'_field'.$key.'" id="'.qa_eaf::field_required.'_field'.$key.'"',
					'note' => qa_lang(qa_eaf::lang.'/'.qa_eaf::field_required.'_note',$key),
					'error' => $error_required[$key],
				);
			}
			
			$buttons = array();
			$buttons['save'] = array(
				'label' => qa_lang_html(qa_eaf::lang.'/'.qa_eaf::save_button),
				'tags' => 'name="'.qa_eaf::save_button.'" id="'.qa_eaf::save_button.'"',
			);
			$buttons['reset'] = array(
				'label' => qa_lang_html(qa_eaf::lang.'/'.qa_eaf::dfl_button),
				'tags' => 'name="'.qa_eaf::dfl_button.'" id="'.qa_eaf::dfl_button.'"',
			);
			
			$this->content['form'] = array(
				'tags' => 'method="post" action="'.qa_path_html(qa_request()).'"',
				'style' => 'wide',
				'fields' => $fields,
				'buttons' => $buttons
			);
			
			if($saved != '' && !$error)
				$this->content['form']['ok'] = $saved;

			return $this->content;
		}
		
		
	}

/*
	Omit PHP closing tag to help avoid accidental output
*/
