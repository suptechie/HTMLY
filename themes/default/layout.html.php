<!DOCTYPE html>
<html>
<head>
	<title><?php echo isset($title) ? _h($title) : config('blog.title') ?></title>
	<link href='<?php echo site_url() ?>favicon.ico' rel='icon' type='image/x-icon'/>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" user-scalable="no" />
	<meta name="description" content="<?php echo $description; ?>" />
	<link rel="canonical" href="<?php echo $canonical; ?>" />
	<link rel="alternate" type="application/rss+xml" title="<?php echo config('blog.title')?> Feed" href="<?php echo site_url()?>feed/rss" />
	<link href="<?php echo site_url() ?>themes/default/css/style.css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Droid+Serif:400,700,400italic|Open+Sans:700,400"/>
	<?php if (publisher() == true):?><link href="<?php echo publisher() ?>" rel="publisher" /><?php endif;?>
	<!--[if lt IE 9]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>
<body class="<?php echo $bodyclass; ?>">
	<div itemscope="itemscope" itemtype="http://schema.org/Blog" class="hide">
		<meta content="<?php echo config('blog.title') ?>" itemprop="name"/>
		<meta content="<?php echo config('blog.description')?>" itemprop="description"/>
	</div>
	<div id="outer-wrapper">
		<div id="menu-wrapper">
			<div class="container">
				<nav id="menu">
					<?php echo menu() ?>
					<form id="search-form" method="get">
						<input type="text" class="search-input" name="search" value="Search..." onfocus="if (this.value == 'Search...') {this.value = '';}" onblur="if (this.value == '') {this.value = 'Search...';}">
						<input type="submit" value="Search" class="search-button">
					</form>
					<?php if(isset($_GET['search'])) {$url = site_url() . 'search/' . $_GET['search']; header ("Location: $url");} ?>
				</nav>
			</div>
		</div>
		<div id="header-wrapper">
			<div class="container">
				<header id="header">
					<section id="branding">
						<h1 class="blog-title"><a href="<?php echo site_url() ?>"><?php echo config('blog.title') ?></a></h1>
						<div class="description"><p><?php echo config('blog.description')?></p></div>
					</section>
				</header>
			</div>
		</div>
		<div id="content-wrapper">
			<div class="container">
				<section id="content">
					<?php echo content()?>
				</section>
			</div>
		</div>
		<div id="footer-wrapper">
			<div class="container">
				<footer id="footer">
					<div class="footer-column">
						<div class="archive column"><div class="inner"><?php echo archive_list()?></div></div>
						<div class="tagcloud column"><div class="inner"><?php echo tag_cloud()?></div></div>
						<div class="social column"><div class="inner"><h3>Follow</h3><?php echo social()?></div></div>
					</div>
					<div class="copyright"><?php echo copyright() ?></div>
				</footer>
			</div>
		</div>
	</div>
	<?php if (analytics() == true):?><?php echo analytics() ?><?php endif;?>
</body>
</html>