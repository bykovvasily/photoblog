<?php
    //prepare data
    require('getContent.php');

    //find it's worong content type, redirect
    if (!(!$_GET['content_type'] OR (in_array($_GET['content_type'], $contentCatalogNamesList) OR in_array($_GET['content_type'], $pageNamesList)))) {
        header("Location: http://bykovvasily.ru/404");
    }

    function createDefaultData() {
        global $contentType, $catalogTitle, $photoId, $setContent;

        //default
        $contentType = 'photostream';
        $catalogTitle = $contentType;
        $photoId = 0;
        $setContent = false;
    }

    switch(true) {
        case in_array($_GET['content_type'], $contentCatalogNamesList);
            //echo 'User with nilk to content';
            //if it's link with data
            $contentType = $_GET['content_type'];
            if ($contentType * 1 > 0) {
                $catalogTitle = $arrayData[$contentType]['title']['_content'];
                $setContent = true;
            } else {
                $catalogTitle = $contentType;
            }

            //search id in list of this catalog
            $photoId = 0;
            foreach($arrayData[$contentType]['photos']['photo'] as $key => $value) {
                if ($_GET['photo_id'] == $value['id']) {
                    break;
                }
                $photoId++;

                //if wrong photo id
                if (count($arrayData[$contentType]['photos']['photo']) == $photoId) {
                    header("Location: http://bykovvasily.ru/404");
                }
            }

            $hidePic = 'false';
        break;
        case in_array($_GET['content_type'], $pageNamesList);
            //echo 'It's text page or sets';
            createDefaultData();
            $hidePic = 'true';
        break;
        default:
            //echo 'Home page';
            createDefaultData();
            $hidePic = 'false';
        break;
    }

    $photosListTotal = $arrayData[$contentType]['photos']['total'];
    $photosListCurrent = $photoId + 1;

    $currentPhotoItem = $arrayData[$contentType]['photos']['photo'][$photoId];

    $picUrl = 'http://farm' . $currentPhotoItem['farm'] . '.staticflickr.com/' . $currentPhotoItem['server'] . '/' . $currentPhotoItem['id'] . '_' . $currentPhotoItem['secret'] . '_b.jpg';
    $picOgImage = 'http://farm' . $currentPhotoItem['farm'] . '.staticflickr.com/' . $currentPhotoItem['server'] . '/' . $currentPhotoItem['id'] . '_' . $currentPhotoItem['secret'] . '_c.jpg';
    $picTitle = htmlspecialchars($currentPhotoItem['title']);
    $picDate = strtolower(date("d F Y", $currentPhotoItem['dateupload']));
    $picSetLink = $currentPhotoItem['setid'];
    $picSetTitle = htmlspecialchars($currentPhotoItem['settitle']);

    //crete data for page
    $main = Array(
        'title' => 'Vasily Bykov // photostream',

        'pic' => $picUrl,
        'picTitle' => $picTitle,
        'picDate' => $picDate,
        'picSetLink' => $picSetLink,
        'picSetTitle' => $picSetTitle,

        'photosListTotal' => $photosListTotal,
        'photosListCurrent' => $photosListCurrent
    );

    switch(true) {
        case in_array($_GET['content_type'], $contentCatalogNamesList);
            //echo 'User with nilk to content';
            $main['title'] = 'Vasily Bykov // ' . $catalogTitle . ', ' . $picDate;
            $meta = Array(
                'ogUrl' => 'http://bykovvasily.ru/' . $contentType . '/' . $_GET['photo_id'],
                'ogImage' => $picOgImage,
                'ogTitle' => $picDate . ', ' . $catalogTitle,
                'ogDescription' => $picTitle,
                'ogImageType' => 'jpg'
            );
        break;
        case (!$_GET OR in_array($_GET['content_type'], $pageNamesList));
            //echo 'It's homepage, or text page, or sets';
            $meta = Array(
                'ogUrl' => 'http://bykovvasily.ru' . '/' . $_GET['content_type'],
                'ogImage' => 'http://bykovvasily.ru/img/bykovvasily.png',
                'ogTitle' => 'Vasily Bykov // photostream',
                'ogDescription' => 'Vasily Bykov\'s personal photoblog. Street, portraits, space.',
                'ogImageType' => 'png'
            );
        break;
    }
?><!DOCTYPE html>
<html>
<head>
    <base href="/" />
    <title><?php echo $main['title']; ?></title>

    <link rel="stylesheet" type="text/css" href="/css/style.css?cach_crash=<?php echo $versionStamp; ?>" />
    <link rel="shortcut icon" href="http://bykovvasily.ru/favicon.ico" type="image/x-icon" />
    <!-- scroll -->
    <link href="/css/jquery.mCustomScrollbar.css?cach_crash=<?php echo $versionStamp; ?>" rel="stylesheet" type="text/css" />

    <meta charset="utf-8" />
    <meta name="robots" content="index, follow" />
    <meta name="rating" content="general" />
    <meta name="keywords" content="photo, photoblog, vasily bykov, photostream, street-photo, street, traveling, russia, фото, фотоблог, василий быков, фотопоток, уличные фото, улица, путешествия, россия" />
    <meta name="description" content="Vasily Bykov's personal photoblog. Street, portraits, space." />
    <meta name="author" content="Vasily Bykov" />
    <!-- openGraph meta -->
    <meta property="og:url" content="<?php echo $meta['ogUrl']; ?>" />
    <meta property="og:image" content="<?php echo $meta['ogImage']; ?>" />
    <meta property="og:image:type" content="image/<?php echo $meta['ogImageType']; ?>" />
    <meta property="og:image:width" content="75" />
    <meta property="og:image:height " content="75" />
    <meta property="og:site_name" content="Bykov Vasily photostream"/>
    <meta property="og:title" content="<?php echo $meta['ogTitle']; ?>" />
    <meta property="og:description" content="<?php echo $meta['ogDescription']; ?>" />
    <meta property="og:type" content="website" />

    <script>
        //fix html5 tags in ie8 and lower
        if (navigator.userAgent.match(/MSIE\s(?!9.0)/)) {
            document.createElement('section');
        }
    </script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script type="text/javascript">
        //if google in down
        if (typeof jQuery == 'undefined') {
            document.write(unescape("%3Cscript src='/js/jquery-1.10.2.min.js' type='text/javascript'%3E%3C/script%3E"));
        }
    </script>
    <!-- cookie plugin -->
    <script src="/js/jquery.cookie.js"></script>
    <!-- scroll -->
    <script src="/js/jquery.mCustomScrollbar.js"></script>
    <!-- main functional -->
    <script>
        //start temp values
        var temp = {
            ajaxrequest: true,
            <?php if ($_GET) { ?>
            current_photo: <?php echo $photoId; ?>,
            current_photo_url_index: <?php echo $photoId; ?>,
            current_list_lenght: <?php echo $photosListTotal; ?>,
            splashsScreen: false,
            <?php } else { ?>
            current_photo: 0,
            current_photo_url_index: 0,
            current_list_lenght: 0,
            splashsScreen: true,
            <?php } ?>
            <?php if ($setContent) { ?>
            setContent: true,
            <?php } else { ?>
            setContent: false,
            <?php } ?>
            versionStamp: '<?php echo $versionStamp; ?>',
            content_type: '<?php echo $contentType; ?>',
            current_content_type: '',
            start_load_array: []
        };
    </script>
    <script src="/js/main.js?cach_crash=<?php echo $versionStamp; ?>"></script>
</head>
<body>
    <div class="wrapperOverflow">
    <section class="splash_screen"<?php if ($_GET) {echo ' style="display: none;"';} ?>>
        <div class="progress"></div>
        <h1>Bykov Vasily photostream</h1>
    </section>
    <nav>
        <ul>
            <li<?php if($contentType == 'photostream' AND $hidePic == 'false') {echo ' class="selected"';} ?> data-catalog_type="photostream" data-content_type="tag_content"><span class="wrapper_selected"><span class="wrapper_underline">photostream</span></span></li>
            <li class="sub_cat<?php if($contentType == 'street') {echo ' selected';} ?>" data-catalog_type="street" data-content_type="tag_content"><span class="wrapper_selected"><span class="wrapper_underline">street</span></span></li>
            <li class="sub_cat<?php if($contentType == 'portrait') {echo ' selected';} ?>" data-catalog_type="portrait" data-content_type="tag_content"><span class="wrapper_selected"><span class="wrapper_underline">portrait</span></span></li>
            <li class="sub_cat<?php if($contentType == 'space') {echo ' selected';} ?>" data-catalog_type="space" data-content_type="tag_content"><span class="wrapper_selected"><span class="wrapper_underline">space</span></span></li>
            <li class="sets<?php if($_GET['content_type'] == 'sets') {echo ' selected';} ?>" data-catalog_type="sets" data-content_type="sets"><span class="wrapper_selected"><span class="wrapper_underline">sets</span></span></li>
            <?php
                if ($setContent) {
                    $subTitle = $catalogTitle;
                    $strLimit = 15;
                }
            ?>
            <li class="set_catalog<?php if ($setContent) {echo ' selected';} ?>"<?php if ($setContent) {echo ' data-catalog_type="' . $_GET['content_type'] . '"';} ?><?php if($setContent AND strlen($subTitle) > $strLimit) {echo ' title="' . $catalogTitle . '"';} ?>><span class="wrapper_selected"><span class="wrapper_underline"><?php if ($setContent) {
                            echo $subTitle;
                        } ?></span></span></li>
            <li class="about<?php if($_GET['content_type'] == 'about') {echo ' selected';} ?>" data-catalog_type="about" data-content_type="content"><span class="wrapper_selected"><span class="wrapper_underline">about</span></span></li>
        </ul>
    </nav>
    <section class="images_list_index"<?php if($hidePic == 'true') {echo ' style="display: none;"';} ?>>
        <div class="current_index">
            <div class="list_index"><?php echo $main['photosListTotal']; ?></div>
            <div class="separate">&nbsp;/&nbsp;</div>
            <div class="wrapper_overflowHidden">
                <div class="wrapper_rolling"><?php echo $main['photosListCurrent']; ?></div><div class="new_num to_next"></div>
            </div>
        </div>
    </section>
    <section class="main_content">
        <div class="wrapper_padding">
            <div class="wrapper_overflowHidden">
                <div class="wrapper_center">
                    <div class="wrapper_pic_conteiner">

                        <div class="pic_conteiner" data-content_type="tag_content"<?php if($hidePic == 'true') {echo ' style="display: none;"';} ?>>
                            <img src="<?php echo $main['pic']; ?>" class="pic" alt="" />
                            <div data-listing_vector="prev" class="pic_nav prev"><div class="pic_nav_arrows"><div class="wrapper_relative"><div class="wrapper_arrow">&larr;&nbsp;</div></div></div></div>
                            <div data-listing_vector="next" class="pic_nav next"><div class="pic_nav_arrows"><div class="wrapper_relative"><div class="wrapper_arrow">&nbsp;&rarr;</div></div></div></div>
                            <div class="desc title">
                                <p class="text"><?php echo $main['picTitle']; ?></p>
                                <p class="date"><?php echo $main['picDate']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="content_page about" data-content_type="about"<?php if($_GET['content_type'] == 'about') {echo ' style="display: block;"';} ?>>
                        <img src="/img/bykovvasily_big.jpg" class="preview_pic" />
                        <p>&mdash; Hi!</p>
                        <p>I really do not like being called a photographer. Although I miss when I'm don't shooting a long time. And almost always in my bag, one or two cameras. Usually a small digital "soap box" with a wide angle, and "canon 5d mII" with a 24-105mm lens. Sometimes i shooting with film cameras, but it's for only fun.</p>
                        <p>I tend to travel a lot. As much as I can. And looking for originality in the "day to day things".</p>
                        <p>I always open for invite to unusual events. Maybe theatrical, news, promotion, sports, fashion... etc events. I looking for interesting moments and peoples, i don't looking for money (I shooting for free).
                        <br />Even if you're a member of any publications or media &mdash; if you think that you know something interested, write me email! </p>

                        <ul>
                            <li><em>email:</em> bykovvasily@gmail.com (most preferred method of contact, i always fast reply to emails)</li>
                            <li><em>photostream mirrors:</em> <a href="http://www.flickr.com/photos/bykovvasili" target="_blank">flickr</a></li>
                            <li><em>my pages:</em> <a href="https://facebook.com/bykovvasily" target="_blank">facebook</a>, <a href="http://vk.com/bykovvasily" target="_blank">vkontakte</a>, <a href="http://lnkd.in/nEabEM" target="_blank">linkedin</a>, <a href="https://twitter.com/elected" target="_blank">twitter</a>, <a href="http://instagram.com/bykovvasily" target="_blank">instagram</a> &mdash; connect!</li>
                            <li><em>phone:</em> +7 (915) 409 94 38 (please, use only if its very urgent)</li>
                        </ul>
                    </div>
                    <div class="content_page sets" data-content_type="sets"<?php if($_GET['content_type'] == 'sets') {echo ' style="display: block;"';} ?>>
                        <div class="wrapper_separate top_line"></div>
                        <div class="wrapper_separate bottom_line"></div>
                        <ul class="sets_list<?php if ($_GET['content_type'] == 'sets') {echo ' visible';} ?>">
                            <?php
                                foreach ($arrayData as $key => $value) {
                                    if (is_numeric($key)) {
                                        echo '
                                        <li class="set_item" data-link="' . $value['id'] . '">
                                            <div class="set_title"><span class="wrapper_underline">' . $value['title']['_content'] . '</span></div>
                                            <div class="set_date">' . strtolower(date("d F Y", $value['date_last_upload'])) . '</div>
                                            <ul class="preview_list">';
                                                $imgIndex = 0;
                                                foreach ($value['photos']['photo'] as $key => $value) {
                                                    echo '<li><img src="http://farm' . $value['farm'] . '.staticflickr.com/' . $value['server'] . '/' . $value['id'] . '_' . $value['secret'] . '_m.jpg" data-img_index="' . $imgIndex . '" /></li>';
                                                    $imgIndex++;
                                                }
                                            echo '</ul>
                                        </li>';
                                    } else {
                                        continue;
                                    }
                                }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>
</body>
<script>
    (function($){
        $(window).load(function(){
            $(".sets_list").mCustomScrollbar({
                theme: 'dark-2',
                scrollInertia: 500,
                mouseWheelPixels: 250,
                autoDraggerLength: true,
                autoHideScrollbar: false,
                scrollButtons:{
                    enable: false,
                    scrollType: 'continuous'
                },
                advanced:{
                    updateOnBrowserResize: true,
                    updateOnContentResize: true,
                    autoScrollOnFocus: true
                }
            });
        });
    })(jQuery);
</script>
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-42075462-1', 'bykovvasily.ru');
    ga('send', 'pageview');
</script>
</html>