<?php

function qa_tag_html($tag, $microdata=false, $favorited=false)
{
   // return qa_tag_html_base('_'.$tag.'_', $microdata, $favorited);
   require_once QA_INCLUDE_DIR.'qa-util-string.php';
   $taghtml=qa_tag_html_base($tag, $microdata, $favorited);

    require_once QA_INCLUDE_DIR.'qa-db-metas.php';

    $description=qa_db_tagmeta_get($tag, 'description');
   // $description=qa_shorten_string_line($description, qa_opt('plugin_tag_desc_max_len'));

    if (strlen($description)) {
        $anglepos=strpos($taghtml, '>');
        if ($anglepos!==false)
            $taghtml=substr_replace($taghtml, ' TITLE="'.qa_html($description).'"',
        $anglepos, 0);
    }

    return $taghtml;

}