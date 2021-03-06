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
	<link rel="stylesheet" type="text/css" title="" href="#{$style_path}css/dialogue.css">
                            
</head>

<body class="wrap">

#{$body_first}
#{$sr_link}

<!-- Body
================================================== -->
<div class="container-narrow wrapper" id="contents">
	#{$body}
</div>

<aside>
	#{$summary}
</aside>



#{$body_last}

<!-- Script
================================================== -->
#{$jquery_script}
#{$bootstrap_script}
#{$common_script}
#{$admin_script}

#{$plugin_script}

</body>
</html>
