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

<body data-spy="scroll" data-target=".bs-docs-sidebar" class="app-config-body">

#{$body_first}
#{$sr_link}


<!-- Body
================================================== -->
<div class="haik-admin-main container-fluid" id="contents">
	<div class="row">
  		<div class="col-sm-8 col-sm-pull-1 content-wrapper pull-right" role="main">
			#{$body}
		</div>
		#{$menu}
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
