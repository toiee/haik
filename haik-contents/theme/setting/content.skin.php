<head>
	#{$meta_content_type}
	<title>#{$page_title}</title>
	#{$viewport}
	<meta name="author" content="">
	
	<link rel="alternate" type="application/rss+xml" title="RSS" href="#{$rss_link}">
	
	<!-- Le styles -->
	#{$bootstrap_css}
	#{$style_css}
	
	#{$head_tag}
	#{$plugin_head}
</head>

<body data-spy="scroll" data-target=".bs-docs-sidebar">

#{$body_first}
#{$sr_link}


<!-- Body
================================================== -->
<div class="container" id="contents">
	<div class="row">
		<div class="col-sm-9 col-sm-push-3 content-wrapper" role="main">
			#{$body}
		</div>
		<aside class="col-sm-3 col-sm-pull-9">
			#{$menu}
		</aside>
	</div>
</div>

<aside>
	#{$summary}
</aside>


<!-- Footer
================================================== -->
<footer class="footer">
	<div class="container">
		#{$site_footer}
	</div>
	<div id="license" class="container">
		#{$license_tag}
	</div>
</footer>

#{$admin_nav}
#{$body_last}

<!-- Script
================================================== -->
#{$jquery_script}
#{$bootstrap_script}
#{$common_script}
#{$admin_script}

#{$plugin_script}
<script src="#{$style_path}skin.js"></script>

</body>
</html>
