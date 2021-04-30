<?php
/*
	Question2Answer by Gideon Greenspan and contributors
	http://www.question2answer.org/

	File: qa-theme/Candy/qa-theme.php
	Description: Override base theme class for Classic theme


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.question2answer.org/license.php
*/

class qa_html_theme extends qa_html_theme_base
{
	// use new ranking layout
	protected $ranking_block_layout = true;
	protected $theme = 'armada';

function logo()
{
	$this->output('<div class="qa-logo">');
	$this->output('<a href="./index.php" class="qa-logo-link"><img src="qa-theme\Armada\Armada.jpg" style="width: 300px;height: 100px;"/> </a>');
	$this->output('</div>');
   
}

function nav($navtype, $level = null)
{	
	if($navtype=='unanswered')
	{

	}
	else
	{
		qa_html_theme_base::nav($navtype, $level = null);
	}
}

function attribution()
{

}

 function footer()
{
	$this->output('<div class="qa-footer">');

	
	$this->attribution();
	$this->footer_clear();

	$this->output('</div> <!-- END qa-footer -->', '');
}



public function feed()
{
	$feed = @$this->content['feed'];

	if (!empty($feed)) {
		$this->output('<img src="qa-theme\Armada\Armada.jpg" style="width: 80px;height: 30px;"/>');
		$this->output('<a href="' . $feed['url'] . '" class="qa-feed-link">' . @$feed['label'] . '</a>');
		
		
	}
}


 function sidebar()
	{
		$sidebar = @$this->content['sidebar'];

		if (!empty($sidebar)) {
			$this->output('<div class="qa-sidebar">');
			$this->output_raw('Welcome to Armada site where you can ask a question and get reliable answers by checking their linkedin profile ');
			$this->output('</div>', '');
		}
	}

}