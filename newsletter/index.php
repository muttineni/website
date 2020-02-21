<?php
$page_info['section'] = 'newsletter';
$page_info['page'] = 'index';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);

print $header;
?>
<!-- begin page content -->
<div class="container">
	<h1><?php print $lang['newsletter']['index']['heading']['newsletter'] ?></h1>
	<div class="row">
		<div class="col p-4">
			<div class="embed-responsive embed-responsive-16by9">
            	<iframe class="embed-responsive-item" src="https://www.youtube-nocookie.com/embed/W7MA7Iykke8?rel=0" frameborder="0" allowfullscreen></iframe>
        	</div>
        	<h3 class="text-center">November 2017</h3>
		</div>
	</div>
	<div class="row">
		<div class="col-md-6 p-4">
        	<div class="embed-responsive embed-responsive-16by9">
            	<iframe class="embed-responsive-item" src="https://www.youtube-nocookie.com/embed/xZ4ZporYIYA?rel=0" frameborder="0" allowfullscreen></iframe>
        	</div>
        	<h3 class="text-center">January 2017</h3>
		</div>		
		<div class="col-md-6 p-4">
        	<div class="embed-responsive embed-responsive-16by9">
            	<iframe class="embed-responsive-item" src="https://www.youtube-nocookie.com/embed/L5PXJVPzZiw?rel=0" frameborder="0" allowfullscreen></iframe>
        	</div>
        	<h3 class="text-center">May 2016</h3>
		</div>
	</div>	
	<div class="row">
		<div class="col-md-6 p-4">
        	<div class="embed-responsive embed-responsive-16by9">
            	<iframe class="embed-responsive-item" src="https://www.youtube-nocookie.com/embed/m01RXetJa9I?rel=0" frameborder="0" allowfullscreen></iframe>
        	</div>
        	<h3 class="text-center">February 2016</h3>
		</div>		
		<div class="col-md-6 p-4">
        	<div class="embed-responsive embed-responsive-16by9">
            	<iframe class="embed-responsive-item" src="https://www.youtube-nocookie.com/embed/W-xqrd0qQJw?rel=0" frameborder="0" allowfullscreen></iframe>
        	</div>
        	<h3 class="text-center">October 2015</h3>
		</div>
	</div>	
	<div class="row">
		<div class="col-md-6 p-4">
        	<div class="embed-responsive embed-responsive-16by9">
            	<iframe class="embed-responsive-item" src="https://www.youtube-nocookie.com/embed/BQTPUDQ4kns?rel=0" frameborder="0" allowfullscreen></iframe>
        	</div>
        	<h3 class="text-center">September 2015</h3>
		</div>		
		<div class="col-md-6 p-4">
        	<div class="embed-responsive embed-responsive-16by9">
            	<iframe class="embed-responsive-item" src="https://www.youtube-nocookie.com/embed/8yjJk0idHCI?rel=0" frameborder="0" allowfullscreen></iframe>
        	</div>
        	<h3 class="text-center">December 2014</h3>
		</div>
	</div>	
	<div class="row">
		<div class="col-md-6 p-4">
        	<div class="embed-responsive embed-responsive-16by9">
            	<iframe class="embed-responsive-item" src="https://www.youtube-nocookie.com/embed/t6syYTDusu4?rel=0" frameborder="0" allowfullscreen></iframe>
        	</div>
        	<h3 class="text-center">September/October 2014</h3>
		</div>		
		<div class="col-md-6 p-4">
        	<div class="embed-responsive embed-responsive-16by9">
            	<iframe class="embed-responsive-item" src="https://www.youtube-nocookie.com/embed/uMdNSh9MwoI?rel=0" frameborder="0" allowfullscreen></iframe>
        	</div>
        	<h3 class="text-center">August/September 2014</h3>
		</div>
	</div>
	<div class="row">			
		<div class="col-md-6 p-4">
        	<div class="embed-responsive embed-responsive-16by9">
            	<iframe class="embed-responsive-item" src="https://www.youtube-nocookie.com/embed/mH1sB5uj7Ok?rel=0" frameborder="0" allowfullscreen></iframe>
        	</div>
        	<h3 class="text-center">September 2013</h3>
		</div>
		<div class="col-md-6 p-4">
        	<div class="embed-responsive embed-responsive-16by9">
            	<iframe class="embed-responsive-item" src="https://www.youtube-nocookie.com/embed/CATeINn8HeI?rel=0" frameborder="0" allowfullscreen></iframe>
        	</div>
        	<h3 class="text-center">March 2014</h3>
		</div>
	</div>
	<div class="row my-3">		
		<div class="col-md-6 p-4">
        	<div class="embed-responsive embed-responsive-16by9">
            	<iframe class="embed-responsive-item" src="https://www.youtube-nocookie.com/embed/JNwzruR27jM?rel=0" frameborder="0" allowfullscreen></iframe>
        	</div>
        	<h3 class="text-center">March 2013</h3>
		</div>
		<div class="col-md-6 p-4">
        	<div class="embed-responsive embed-responsive-16by9">
            	<iframe class="embed-responsive-item" src="https://www.youtube-nocookie.com/embed/v3F_S9GbUbY?rel=0" frameborder="0" allowfullscreen></iframe>
        	</div>
        	<h3 class="text-center">December 2009</h3>
		</div>
	</div>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
