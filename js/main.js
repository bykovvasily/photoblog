$(document).ready(function() {
    var main = {
            viewport: $('body'),

            //nav menu
            nav: $('nav'),
            nav_menu: $('nav ul li'),
            nav_hint_subSet: $('nav ul li.set_catalog'),

            //manual movie
            manual: $('section.manual'),
            manual_cursor_blink_list: $('section.manual .movie .cursor .wrapper_background'),
            manual_mouse_blink_item: $('section.manual .mouse .left_buttom .wrapper_background'),

            //list index
            list_index: $('section.images_list_index'),
            list_index_current: $('section.images_list_index .current_index .wrapper_rolling'),
            list_index_new_num: $('section.images_list_index .current_index .new_num'),
            list_index_total: $('section.images_list_index .list_index'),

            //splash screen
            splash_screen: $('section.splash_screen'),
            splash_screen_progress: $('section.splash_screen .progress'),

            //for all content box
            main_content: $('section.main_content'),

            //main pic
            wrapper_pic_conteiner: $('section.main_content .wrapper_pic_conteiner'),
            pic_conteiner: $('section.main_content .pic_conteiner'),
            pic: $('section.main_content .pic'),
            pic_title: $('section.main_content .title'),
            pic_title_text: $('section.main_content .title .text'),
            pic_title_date: $('section.main_content .title .date'),
            pic_nav: $('section.main_content .pic_nav'),
            pic_listing_speed: 200,

            //selector for all content pages
            content_page: $('section.main_content .content_page'),

            //sets page vars
            sets_list: $('.sets_list'),
            sets_list_items: $('.sets_list .set_item'),

            //base meta
            metaTitlePart: 'Vasily Bykov // '
        },
        flickr = {
            content: {}
        };
    temp.current_nav = main.nav.find('.selected');//by php
    temp.current_content_type = temp.current_nav.data('content_type');//by php

    //
    //functions
    //
    //Function for right date format
    Date.prototype.monthNames = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];
    Date.prototype.getMonthName = function() {
        return this.monthNames[this.getMonth()];
    };
    /*    Date.prototype.getShortMonthName = function () {
     return this.getMonthName().substr(0, 3);
     };*/
    Date.prototype.format = function(format) {
        var o = {
            "M+" : this.getMonth()+1, //month
            "d+" : this.getDate(),    //day
            "q+" : Math.floor((this.getMonth()+3)/3)  //quarter
        }

        if(/(y+)/.test(format)) format=format.replace(RegExp.$1,
            (this.getFullYear()+"").substr(4 - RegExp.$1.length));
        for(var k in o)if(new RegExp("("+ k +")").test(format))
            format = format.replace(RegExp.$1,
                RegExp.$1.length==1 ? o[k] :
                    ("00"+ o[k]).substr((""+ o[k]).length));
        return format;
    }

    //function changeMetaDate
    function getMetaData(metaTitle) {
        $('meta[property="og:title"]').attr('property', metaTitle);
    }

    //movie
    function manualMovie() {
        var cursorActionIndex = 0,
            mouseBlinkIndex = 3;

        setTimeout(function() {
            (function blinkMouse() {
                if (mouseBlinkIndex) {
                    mouseBlinkIndex--;
                    main.manual_mouse_blink_item.fadeIn('fast').fadeOut('fast');
                    setTimeout(function() {
                        blinkMouse();
                    }, 300);
                }
            })();

            (function blinkCursor(index) {
                if(main.manual_cursor_blink_list.length >= index) {
                    $(main.manual_cursor_blink_list[index]).fadeIn('fast').fadeOut('fast');
                    cursorActionIndex++;
                    setTimeout(function() {
                        blinkCursor(cursorActionIndex);
                    }, 300);
                }
            })(cursorActionIndex);
        }, 250);
    }

    function getVerticalHeight() {
        main.main_content.css('height', main.viewport.height() - (parseFloat(main.main_content.css('top'))));
        main.pic.
            add(main.wrapper_pic_conteiner).
            add(main.content_page).
            css('height', main.viewport.height() - (parseFloat(main.main_content.css('top')) * 2));
    }

    function changeCurrentIndex(vector) {
        //fix double request
        //Must refacted
        //if loaading img OR pic hidden
        if(temp.ajaxrequest || main.pic_conteiner.is(':hidden')) {
            return false;
        }

        if(vector == 'next') {
            if(!main.list_index_new_num.hasClass('to_next')) {
                if(main.list_index_new_num.hasClass('to_prev')) {
                    main.list_index_new_num.removeClass('to_prev');
                }
                main.list_index_new_num.addClass('to_next');
            }

            if(temp.current_photo + 1 == flickr.content[temp.content_type].photos.total * 1) {
                temp.current_photo = 0;
            } else {
                temp.current_photo += 1;
            }
        } else {
            if(!main.list_index_new_num.hasClass('to_prev')) {
                if(main.list_index_new_num.hasClass('to_next')) {
                    main.list_index_new_num.removeClass('to_next');
                }
                main.list_index_new_num.addClass('to_prev');
            }

            if(temp.current_photo - 1 < 0) {
                temp.current_photo = flickr.content[temp.content_type].photos.total - 1;
            } else {
                temp.current_photo -= 1;
            }
        }

        var pxRollingIndex = (vector == 'next') ? -20 : 20;
        pxRollingIndex += 'px';

        main.list_index_new_num.text(temp.current_photo + 1);

        main.list_index_current.animate({
            top: pxRollingIndex
        }, 350);

        main.list_index_new_num.animate({
            top: 0
        }, 350, function() {
            main.list_index_current.text(temp.current_photo + 1);
            main.list_index_current.css('top', 0);
            main.list_index_new_num.attr('style', '');
        });

        loadImg(temp.current_photo);
    }

    function getContent() {
        $.getJSON('/jsonData.txt?cach_crash=' + temp.versionStamp).done(function(response) {
            /*            for(key in response) {
             flickr.content[key] = response[key];
             }*/
            flickr.content = response;

            //when all request done
            if (temp.splashsScreen) {
                collapseSplashscreen();
            } else {
                temp.ajaxrequest = false;
                getVerticalHeight();
            }
        });
    };

    function collapseSplashscreen(progress) {
        if(temp.start_load_array.length) {
            main.splash_screen_progress.animate({
                width: Math.floor(progress) + '%'
            }, 300, function() {
                if(Math.floor(progress) < 1) {
                    getVerticalHeight();
                    temp.ajaxrequest = false;
                    main.splash_screen.fadeOut(300);
                }
            });
        } else {
            manualMovie();

            //generate preload list
            var imgToLoad_1 = flickr.content[temp.content_type].photos.photo[0],
                imgToLoad_2 = flickr.content[temp.content_type].photos.photo[1];

            temp.start_load_array.push(generateSrcUrl(imgToLoad_1.farm, imgToLoad_1.server, imgToLoad_1.id, imgToLoad_1.secret), generateSrcUrl(imgToLoad_2.farm, imgToLoad_2.server, imgToLoad_2.id, imgToLoad_2.secret));

            for(key in flickr.content) {
                //this proces only for tag content
                //check, it's set?
                if (key * 1) {
                    //it's set content
                } else {
                    if(key != temp.content_type) {
                        var imgToLoad = flickr.content[key].photos.photo[0];
                        temp.start_load_array.push(generateSrcUrl(imgToLoad.farm, imgToLoad.server, imgToLoad.id, imgToLoad.secret));
                    }
                }
            }
            loadImg(temp.start_load_array);
        }
    }

    function generateSrcUrl(farm, server, id, secret) {
        return 'http://farm' + farm + '.staticflickr.com/' + server + '/' + id + '_' + secret + '_b.jpg';
    }

    navigator.sayswho= (function(){
        var ua = navigator.userAgent,
            N = navigator.appName, tem,
            M = ua.match(/(opera|chrome|safari|firefox|msie|trident)\/?\s*([\d\.]+)/i) || [];
        M = M[2]? [M[1], M[2]]:[N, navigator.appVersion, '-?'];
        if(M && (tem= ua.match(/version\/([\.\d]+)/i))!= null) M[2]= tem[1];
        return  M.join(' ');
    })();

    function urlEdit(url) {
        if (navigator.sayswho.substr(5, 2) * 1 >= 10 || navigator.sayswho.toLowerCase().substr(0, 5) == 'opera' || navigator.sayswho.toLowerCase().substr(0, 6) == 'chrome' ||  navigator.sayswho.toLowerCase().substr(0, 7) == 'firefox') {
            //if ie >= 10 or chrome or opera or firefox
            history.pushState(null, null, url);
        } else {
            return false;
        }
    }

    //img loader
    function loadImg(current_photo_index) {
        if(temp.current_list_lenght != flickr.content[temp.content_type].photos.total) {
            temp.current_list_lenght = flickr.content[temp.content_type].photos.total * 1;
            main.list_index_total.text(temp.current_list_lenght);
        }

        switch (typeof current_photo_index) {
            case ('object'):
                var toLoadLength = current_photo_index.length,
                    progressPart = 100 / toLoadLength,
                    progressIndex = 100;

                for (var i = 0; i < toLoadLength; i++) {
                    var imgToLoad = new Image();

                    imgToLoad.src = current_photo_index[i];

                    //cacheTest
                    if(imgToLoad.complete) {
                        progressIndex -= progressPart;
                        collapseSplashscreen(progressIndex);
                    } else {
                        $(imgToLoad).load(function() {
                            progressIndex -= progressPart;
                            collapseSplashscreen(progressIndex);
                        });
                    }
                }
                break;
            default:
                //fix double request
                //Must refacted
                if(temp.ajaxrequest) {
                    return false;
                }

                //this photo
                var photo_to_load = flickr.content[temp.content_type].photos.photo[current_photo_index],
                    title = photo_to_load.title,
                    settitle = photo_to_load.settitle,
                    setid = photo_to_load.setid,
                    loadUrl = generateSrcUrl(photo_to_load.farm, photo_to_load.server, photo_to_load.id, photo_to_load.secret),
                //preload img
                    img = new Image(),
                    delay_loading = setTimeout(function() {
                        main.pic_conteiner.addClass('loading')
                    }, 500),
                //meta title
                    date_upload = new Date(),
                    metaTitle = '';

                //create meta title
                if(temp.content_type * 1) {
                    metaTitle = flickr.content[temp.content_type].title._content;
                } else {
                    metaTitle = temp.content_type;
                }
                date_upload.setTime(photo_to_load.dateupload * 1000);
                metaTitle = main.metaTitlePart + metaTitle + ', ' + date_upload.format('dd') + '.' + (date_upload.getMonth() + 1001 + '').substr(2)  +  '.' + date_upload.format('yyyy');

                img.src = loadUrl;
                //must refactored, fix ie .load
                if (navigator.appName.indexOf("Internet Explorer")!=-1) {
                    img.src += '?' + new Date().getTime();
                }

                //url index
                temp.current_photo_url_index = temp.current_list_lenght - current_photo_index;
                urlEdit(temp.content_type + '/' + photo_to_load.id);

                //ajax flag
                temp.ajaxrequest = true;

            function loadWitchCache() {
                clearTimeout(delay_loading);
                main.pic_conteiner.removeClass('loading');
                main.pic.attr('src', loadUrl);
                main.pic.animate({
                    opacity: 1
                }, main.pic_listing_speed, function() {
                    //metaTitle edit
                    document.title = metaTitle;
                    getMetaData(metaTitle)
                });
                temp.ajaxrequest = false;
            }

                main.pic.animate({
                    opacity: 0
                }, main.pic_listing_speed, function() {
                    //if it set content - edit nav menu
                    if (temp.content_type * 1 && main.nav_hint_subSet.is(':hidden')) {
                        var setTitleLimit = 15,
                            setTitle = flickr.content[temp.content_type].title._content,
                            setTitleLength = setTitle.length;

                        temp.current_nav.removeClass('selected');
                        temp.setContent = true;
                        temp.current_nav = main.nav_hint_subSet;

                        if (setTitleLength > setTitleLimit) {
                            //kill last space
                            if(setTitle[setTitleLimit - 1] == ' ' || setTitle[setTitleLimit] == '.') {
                                setTitleLimit--;
                            }

                            main.nav_hint_subSet.attr('title', setTitle);
                            //setTitle = setTitle.substr(0, setTitleLimit) + '...';
                        }
                        main.nav_hint_subSet.find('.wrapper_underline').text(setTitle);

                        main.nav_hint_subSet.slideToggle(200).css({'display': 'block'}).animate({
                            opacity: 1
                        }, 200);
                        main.nav_hint_subSet.addClass('selected');
                    }

                    main.pic_title_text.text(title);

                    main.pic_title_date.text(date_upload.format('dd') + ' ' + date_upload.getMonthName()  +  ' ' + date_upload.format('yyyy'));

                    //if was content page
                    if(main.pic_conteiner.is(':hidden')) {
                        main.pic_conteiner.fadeIn(0);
                    }

                    //cacheTest
                    if(img.complete) {
                        loadWitchCache();
                    } else {
                        img.onload = function() {
                            loadWitchCache();
                        }
                    }
                });
                break;
        }
    }

    function changeCatalog(vector) {
        //if we loading other content
        //should refactored
        if (temp.ajaxrequest) {
            return false;
        }

        var selectedCatalog = temp.current_nav;

        if(vector == 'up') {
            if(!!selectedCatalog.prevAll('li:visible')[0]) {
                $(selectedCatalog.prevAll('li:visible')[0]).trigger('click');
            } else {
                return false;
            }
        } else {
            if(!!selectedCatalog.nextAll('li:visible')[0]) {
                $(selectedCatalog.nextAll('li:visible')[0]).trigger('click');
            } else {
                return false;
            }
        }
    }

    //
    //events handlers
    //

    //resize
    window.onresize = function() {
        getVerticalHeight();
    }

    window.onkeydown = function(event) {
        //if we loading other content
        //should refactored
        if (temp.ajaxrequest) {
            return false;
        }

        switch (event.keyCode) {
            case 39:
                //right
                changeCurrentIndex('next');
                break;
            case 37:
                //left
                changeCurrentIndex('prev');
                break;
            case 38:
                //up
                changeCatalog('up');
                break;
            case 40:
                //down
                changeCatalog('down');
                break;
        }
    }

    //nav
    main.nav_menu.click(function() {
        var opacityIndex;

        if($(this).hasClass('selected') || temp.ajaxrequest) {
            return false;
        }

        function hideShowMainpic() {
            if (main.pic_conteiner.is(':visible')) {
                main.pic_conteiner.add(main.list_index).fadeOut(main.pic_listing_speed);
            }
        }

        if (temp.setContent) {
            temp.setContent = false;

            main.nav_hint_subSet.removeAttr('title').slideToggle(200, function() {
                if (temp.setContent) {
                    opacityIndex = 0;
                } else {
                    opacityIndex = 1;
                }
                main.nav_hint_subSet.animate({
                    opacity: opacityIndex
                }, 100);
            });

            main.nav_hint_subSet.removeClass('selected');
        }

        temp.current_nav.removeClass('selected');
        $(this).addClass('selected');

        //hide prev content
        if (temp.current_content_type != $(this).data('content_type')) {
            main.main_content.find('[data-content_type="' + temp.content_type + '"]');
        }

        temp.current_content_type = $(this).data('content_type');
        temp.current_nav = $(this);
        temp.content_type = $(this).data('catalog_type');

        switch ($(this).data('content_type')) {
            case 'tag_content':
                if (temp.ajaxrequest) {
                    return false;
                }

                //hide content pages
                main.content_page.fadeOut(main.pic_listing_speed);

                temp.current_photo = 0;

                //load cat
                loadImg(0); //see first photo in cat
                main.list_index_current.text(1);

                //if was content page
                if(main.pic_conteiner.is(':hidden')) {
                    main.list_index.fadeIn(main.pic_listing_speed);
                    return false;
                }
                break;
            case 'sets':
                //url
                urlEdit($(this).text());

                //title
                document.title = main.metaTitlePart + temp.content_type;

                //hide photostream
                hideShowMainpic();
                //hide content pages
                main.content_page.fadeOut(main.pic_listing_speed);

                //show content page
                $('.' + temp.content_type).fadeIn(main.pic_listing_speed);

                //show set items
            function showSet(setIndex) {
                $(main.sets_list_items[setIndex]).fadeIn(300);

                setTimeout(function() {
                    setIndex++;
                    if ($(main.sets_list_items[setIndex]).length) {
                        showSet(setIndex);
                    } else {
                        //if we shown all items, fix last row
                        var indexToAdddFix = main.sets_list_items[main.sets_list_items.length - 1].offsetTop;

                        for (var i = main.sets_list_items.length - 1; i >= 0; i--) {
                            if (main.sets_list_items[i].offsetTop == indexToAdddFix) {
                                $(main.sets_list_items[i]).addClass('last_row');
                            } else {
                                break;
                            }
                        }
                    }
                }, 100);
            }

                ///fix sets hidden
                if ($(main.sets_list_items[0]).is(':hidden')) {
                    showSet(0);
                }
                break;
            case 'content':
                //url
                urlEdit($(this).text());

                //title
                document.title = main.metaTitlePart + temp.content_type;

                //hide photostream
                hideShowMainpic();
                //hide content pages
                main.content_page.fadeOut(main.pic_listing_speed);

                //show content page
                $('.' + temp.content_type).fadeIn(main.pic_listing_speed);
                break;
        }
    });

    //pic listing
    main.pic_nav.click(function() {
        changeCurrentIndex($(this).data('listing_vector'));
    });

    //sets list
    main.sets_list_items.click(function(event) {
        if (temp.ajaxrequest) {
            return false;
        }

        var imgToShow = ($(event.target).data('img_index')) ? $(event.target).data('img_index') : 0;

        //hide content pages
        main.content_page.fadeOut(main.pic_listing_speed);

        temp.current_nav.removeClass('selected');

        temp.current_content_type = 'tag_content';
        temp.current_photo = imgToShow;
        temp.current_nav = main.nav_menu.find('.sets');
        temp.content_type = $(this).data('link');

        temp.current_nav.addClass('selected');

        //load cat
        loadImg(imgToShow);
        main.list_index_current.text(imgToShow + 1);

        //show list index
        main.list_index.fadeIn(main.pic_listing_speed);
    });

    //
    // launch
    //
    getContent();
});