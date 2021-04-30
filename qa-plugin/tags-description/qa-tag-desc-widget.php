<?php

class qa_tag_descriptions_widget {


    //note that the widget is not added by default, we have the plugin added but the widget we need to add it manually
    function allow_template($template)
    {
        return ($template=='tag');
    }
    function allow_region($region)
    {
    return true;
    }

    function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
    {
    //echo 'just a test';

    require_once QA_INCLUDE_DIR.'qa-db-metas.php';

    $parts=explode('/', $request);
    $tag=$parts[1];

    $description=qa_db_tagmeta_get($tag, 'description');
    $editurlhtml=qa_path_html('tag-edit/'.$tag);

    if (strlen($description)) {
        echo qa_html($description);
        // echo '<SPAN style="font-size:'.(int)qa_opt('plugin_tag_desc_font_size').'px;">';
        // echo qa_html($description);
        // echo '</SPAN>';
        echo ' - <a href="'.$editurlhtml.'">edit</a>';
    } else
        echo '<a href="'.$editurlhtml.'">Create tag description</a>';
    }
    function option_default($option)
{
    if ($option=='plugin_tag_desc_max_len')
        return 250;

    if ($option=='plugin_tag_desc_font_size')
        return 18;

    return null;
}

    function admin_form(&$qa_content)
{
    $saved=false;

    if (qa_clicked('plugin_tag_desc_save_button')) {
        qa_opt('plugin_tag_desc_max_len', (int)qa_post_text('plugin_tag_desc_ml_field'));
        qa_opt('plugin_tag_desc_font_size', (int)qa_post_text('plugin_tag_desc_fs_field'));
        $saved=true;
    }

    return array(
        'ok' => $saved ? 'Tag descriptions settings saved' : null,

        'fields' => array(
            array(
                'label' => 'Maximum length of tooltips:',
                'type' => 'number',
                'value' => (int)qa_opt('plugin_tag_desc_max_len'),
                'suffix' => 'characters',
                'tags' => 'NAME="plugin_tag_desc_ml_field"',
            ),

            array(
                'label' => 'Starting font size:',
                'type' => 'number',
                'value' => (int)qa_opt('plugin_tag_desc_font_size'),
                'suffix' => 'pixels',
                'tags' => 'NAME="plugin_tag_desc_fs_field"',
            ),
        ),

        'buttons' => array(
            array(
                'label' => 'Save Changes',
                'tags' => 'NAME="plugin_tag_desc_save_button"',
            ),
        ),
    );
}

}



