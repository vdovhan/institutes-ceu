$.fn.enterKey = function (fnc) {
    return this.each(function () {
        $(this).keypress(function (ev) {
            var keycode = (ev.keyCode ? ev.keyCode : ev.which);
            if (keycode == '13') {
                fnc.call(this, ev);
            }
        })
    })
}

var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};

jQuery( window ).resize(function() {
  
    if( $('body').width() <= 960 ){ 
        if( !$('.left-sidebar').hasClass('active') ){
            if( $('.bookmarks-menu').hasClass('open') ){
                $('.mobile-nav').click()
            }   
        }
        if( $('.bookmarks-menu').hasClass('open') ){
            $('.bookmarks').click()
        }
    }

})

jQuery(window).ready(function() {

    var doc = document.documentElement;
    doc.setAttribute('data-useragent', navigator.userAgent);

    $('.mobile-nav').click(function () {
        $('.left-sidebar').toggleClass('active');
        $('#page-content.row-fluid').toggleClass('active-sidebar');
        if( $('.left-sidebar').hasClass('active') == false ){
            $('.bookmarks-box').css({'display':'none','opacity':0})
            $('[opened-book="1"]').removeAttr('opened-book')
            if( $('.side-post').hasClass('open') ){
                toggleRightSidebar();
                $('.activecourse').removeClass('active')
            }
        } else {
            
        }
    });

    if( $('body').width() <= 960 && $('body').width() >= 560 ){ 
        $('.mobile-nav').click()
    }

    /*$('.header-notifications-menu ul li:nth-last-child(3)').click(function (e) {
        e.preventDefault(false);
        $(this).toggleClass('active');
    });*/

    $('#page-my-index, #page-course-view-institutes_ceu').on('click', '.pickup', function () {
        var course_id = getUrlParameter('id');
        jQuery.ajax({
            async: false,
            url: "/theme/institutes_ceu/actions/ajax_report_get_last_action.php",
            type: "post",
            data: 'course_id=' + course_id,
            dataType: "json"
        }).done(function (data) {
            if(data.code == '200') {
                window.location = data.url;
            }
        });
    });
    
    $(".questionflag.editable").click(function(e){
        var qid = $(this).parent().parent().attr('id');
        if ($('.quiz-attempt-page .qn_buttons li[data-id="'+qid+'"]').length){
            $('.quiz-attempt-page .qn_buttons li[data-id="'+qid+'"]').toggleClass('hidden');
        }
        
        var isflagged = false;
        $('.quiz-attempt-page .qn_buttons li.qid').each(function(e){
            if (!$(this).hasClass('hidden')){
                isflagged = true;
            }
        });
        
        if (isflagged){
            $('.quiz-attempt-page .qn_buttons li.noq').addClass('hidden');
        } else {
            $('.quiz-attempt-page .qn_buttons li.noq').removeClass('hidden');
        }
    });

    if( $('body').width() <= 960 ){
        $('.activecourse').removeClass('active')
        $('.side-post').removeClass('open')
    }
    // if(window.location.pathname == "/mod/quiz/view.php"){
    // }

    // if( $('body').width() <= 960 ){
    //     $('.bookmarks-box').css({'height':$('body').height(), 'maxHeight':$('body').height()}).appendTo('body')
    // }

    $(document).click(function(event) {
        
        var target = event.target;

        if (!$(target).parents().is('.bookmarks-menu') && !$(target).parents().is('.bookmarks-list') && jQuery('.bookmarks-menu').hasClass('open') && !$(target).is('.bookmarks') && !$(target).closest('.bookmarks').length ) {
            console.log('close bookmark')
            bookmarksClose();
        }

        if( ($(target).is('.actions') || $(target).closest('.actions').length ) && $(target).closest('.main-navigation').length ){
            $('.actions-menu').find('form').submit()
        }

        if( ($(target).is('.bookmarks') || $(target).closest('.bookmarks').length ) && $(target).closest('.main-navigation').length ){
            if(typeof $(target).attr('opened-book') == 'undefined'){
                $('.bookmarks-box').css({'display':'block','opacity':1})
                jQuery('.bookmarks-menu').addClass('open')
                $(target).attr('opened-book',1)
            } else {
                $(target).removeAttr('opened-book')
                jQuery('.bookmarks-menu').removeClass('open')
                $('.bookmarks-box').css({'display':'none','opacity':0})
            }
        }
        
    });

    // submit bookmark on enter
    $('.bookmarks-form form').submit(function(e){
        e.preventDefault();
        bookmarkSave();
    })

});

$(function() {
    $(window).resize(function() {
        if (jQuery('.course-file-box.embed iframe').length){
            var vframe = jQuery('.course-file-box.embed iframe');
            var width = vframe.width();
            var newheigth = width*(9/16);

            jQuery(vframe).css("height", newheigth);
        }
    }).resize();
});

function toggleLeftSidebar(){
    
    jQuery('.left-sidebar').toggleClass('open');
    jQuery('#page.container-fluid').toggleClass('full-width');
    
    if($('.left-sidebar.active').length) {
        $('#page-content.row-fluid.active-sidebar').toggleClass('open');
    }
    M.util.set_user_preference('fix-sidebar', jQuery('.left-sidebar').hasClass('open')?1:0);
}

function toggleSidePre(){
    if (!jQuery('.side-pre-box').hasClass('open') && !jQuery('.toggler-bg').length){
        jQuery('body').prepend('<div class="toggler-bg" onclick="closeMenu();"></div>');
        jQuery('.header-navbar').prepend('<div class="header-toggler-bg" onclick="closeMenu();"></div>');
    } else if (jQuery('.side-pre-box').hasClass('open')) {
         jQuery('.toggler-bg').remove();
        jQuery('.header-toggler-bg').remove();
    }
    jQuery('.side-pre-box').toggleClass('open');
    jQuery('.main-navigation li.preferences').toggleClass('active');
}

function toggleRightSidebar() {
    if (jQuery('.side-post').hasClass('open')){
        jQuery('#region-main-box').addClass('span12 pull-right').removeClass('span9');
        jQuery('#page').addClass('full-width');
        jQuery('.side-post').removeClass('open');
    } else {    
        jQuery('#region-main-box').addClass('span9').removeClass('span12 pull-right');
        jQuery('#page').removeClass('full-width');

        jQuery('.side-post').toggleClass('open');
        // jQuery('.main-navigation li.activecourse').toggleClass('active');
    }
    jQuery('.main-navigation li.activecourse').toggleClass('active');
}

function toggleCourseMenu() {
    if (!jQuery('.left-sidebar').hasClass('open')){
        toggleLeftSidebar();
    }
    
    jQuery('.main-navigation li.activecourse').toggleClass('active');
}

function closeMenu(){
    jQuery('.main-navigation li.preferences').removeClass('active');
    jQuery('.side-pre-box').removeClass('open');   
    jQuery('.left-sidebar').removeClass('open');
    jQuery('.toggler-bg').remove();
    jQuery('.header-toggler-bg').remove();
}

function module_completion(form, change_btn) {
    var status = form.find('input[name="state"]').val();
    var newstatus = form.find('input[name="completionstate"]').val();
    var title = form.find('input[name="title"]').val();
    var newtitle = form.find('input[name="newtitle"]').val();

    form.find('button').html('<i class="fa fa-spinner"></i>');
    $.ajax( {
      type: "POST",
      url: form.attr( 'action' ),
      data: form.serialize(),
      success: function() {
        form.find('input[name="state"]').val(newstatus);
        form.find('input[name="completionstate"]').val(status);
        form.find('input[name="title"]').val(newtitle);
        form.find('input[name="newtitle"]').val(title);
        form.find('button').html(newtitle);

        var status_btn = jQuery('.status-btn');
        status_btn.removeClass('btn-success');
        status_btn.removeClass('btn-danger');
        status_btn.removeClass('btn-warning');
        if (newstatus == '1'){
            status_btn.addClass('btn-success');
            status_btn.text('Completed');
            form.find('button').removeClass('btn-warning');
            form.find('button').addClass('btn-success');

            if(change_btn) {
                form.parent().parent().find('.btn-completion').removeClass('btn-info');
                form.parent().parent().find('.btn-completion').addClass('btn-success');
                form.parent().parent().find('.btn-completion').html('Completed');
            }
        } else {
            status_btn.addClass('btn-warning');
            status_btn.text('In Progress');
            form.find('button').removeClass('btn-success');
            form.find('button').addClass('btn-warning');

            if(change_btn) {
                form.parent().parent().find('.btn-completion').removeClass('btn-success');
                form.parent().parent().find('.btn-completion').addClass('btn-info');
                form.parent().parent().find('.btn-completion').html('Not Started');
            }
        }
      }
    } );
}

function toggleModuleCompletion() {
    var form = jQuery('#toggle_module_completion');
    module_completion(form, false);
}

function toggle_module_completion(modid) {
    var form = jQuery('#toggle_module_completion_'+modid);
    var actionbox = jQuery('#toggle_module_completion_box_'+modid);
    
    var status = form.find('input[name="state"]').val();
    var newstatus = form.find('input[name="completionstate"]').val();
    var title = form.find('input[name="title"]').val();
    var newtitle = form.find('input[name="newtitle"]').val();
    var icontitle = form.find('input[name="icontitle"]').val();
    var newicontitle = form.find('input[name="newicontitle"]').val();

    actionbox.find('.completion-actions-icon').html('<i class="fa fa-spin fa-spinner"></i>');
    $.ajax( {
      type: "POST",
      url: form.attr( 'action' ),
      data: form.serialize(),
      success: function() {
        form.find('input[name="state"]').val(newstatus);
        form.find('input[name="completionstate"]').val(status);
        form.find('input[name="title"]').val(newtitle);
        form.find('input[name="newtitle"]').val(title);
        form.find('input[name="icontitle"]').val(newicontitle);
        form.find('input[name="newicontitle"]').val(icontitle);
          
        actionbox.toggleClass('notcompleted');
        actionbox.toggleClass('completed');
        actionbox.find('.completion-actions-icon').attr('title', icontitle);
        actionbox.find('.completion-actions-text').html(newtitle);
        actionbox.find('.completion-actions-icon').html('');
      }
    } );
}

function _toggleModuleCompletion(modid) {
    toggle_module_completion(modid, true);
}

function bookmarkSave(){
    var name = jQuery('#bookmark_name').val();
    var id = jQuery('#bookmark_id').val();
    var userid = jQuery('#bookmark_userid').val();
    
    var url = jQuery(location).attr('href');
    var encodedUrl = encodeURIComponent(url);
    
    if(name == ''){
        jQuery('#bookmark_name').addClass('req');
    }else{
        $.ajax({
           type: "POST",
           url: window.M.cfg.wwwroot+"/theme/institutes_ceu/ajax.php",
           data: "action=save-bookmark&name="+name+"&id="+id+"&userid="+userid+'&url='+encodedUrl,
           success: function(msg){
               if (id > 0){
                   jQuery('.bookmark-item[data-id="'+id+'"]').removeClass('active');
                   jQuery('.bookmark-item[data-id="'+id+'"] .bookmark-link').text(name);
                   hideEditForm();
               } else {
                   if (!jQuery('.bookmarks-box .bookmarks-list li').length){
                       jQuery('.saved-bookmarks-title').removeClass('hidden');
                       jQuery('.havenotbookmarks').addClass('hidden');
                   }
                   // clean form
                   jQuery('#bookmark_name').val('')
                   // add elem to begin of list
                   jQuery('.bookmarks-list').prepend(msg);
               }
           }
         });
    }
}

function showEditForm(id){

    // if there was edited before element - show it again
    if( $('#bookmark_id').val() > 0 ){
        $('.bookmarks-menu .bookmark-item[data-id="'+$('#bookmark_id').val()+'"]').show()
    }

    var name = $('.bookmarks-menu .bookmark-item[data-id="'+id+'"] .bookmark-link').text();
    $('#bookmark_name').val(name);
    $('#bookmark_id').val(id);

    $('.bookmarks-form').insertAfter('.bookmarks-menu .bookmark-item[data-id="'+id+'"]')
    $('.bookmarks-menu .bookmark-item[data-id="'+id+'"], .bookmarks-form-title').hide()

    if( !$('.hide-edit-form-btn').length ){
        $('<a class="hide-edit-form-btn" onclick="hideEditForm();" href="javascript:void(0);">Cancel</a>').appendTo('.bookmarks-form form')
    } else {
        $('.hide-edit-form-btn').show()
    }

    $('#bookmark_name').select();
}

function hideEditForm(){
    var id = $('#bookmark_id').val();
    $('#bookmark_name').val('');
    $('#bookmark_id').val(0);
    $('.bookmarks-form').insertBefore('.saved-bookmarks-title')
    $('.bookmarks-menu .bookmark-item[data-id="'+id+'"], .bookmarks-form-title').show()
    $('.hide-edit-form-btn').hide()
}

function bookmarksEdit(id){
    if (!jQuery('.bookmarks-menu').hasClass('open')){
        bookmarksOpen();
    }
    
    jQuery('.bookmark-item').not('.current').removeClass('active');
    jQuery('.bookmark-item[data-id="'+id+'"]').addClass('active');
    
    showEditForm(id);
}

function bookmarksDelete(id){
    jQuery('#bookmark_id').val(0);
    if (confirm('Are you sure to delete this bookmark?')){
        $.ajax({
           type: "POST",
           url: window.M.cfg.wwwroot+"/theme/institutes_ceu/ajax.php",
           data: "action=delete-bookmark&id="+id,
           success: function(msg){
              jQuery('.bookmark-item[data-id="'+id+'"]').remove();
               if (!jQuery('.bookmarks-box .bookmarks-list li').length){
                   jQuery('.saved-bookmarks-title').addClass('hidden');
                   jQuery('.havenotbookmarks').removeClass('hidden');
               }
           }
         });   
    } else {
        return false;   
    }
}

function bookmarksOpen(){
    jQuery('.bookmarks-menu').addClass('open');
    jQuery('#bookmark_name').select();
    $('.bookmarks-box').css({'display':'block','opacity':1})
    $('.bookmarks').attr('opened-book',1)
}
function bookmarksClose(){
    jQuery('.bookmarks-menu').removeClass('open');
    $('.bookmarks-box').css({'display':'none','opacity':0})
    $('[opened-book]').removeAttr('opened-book')
}

function quizSubmit(){
    var page = jQuery('#quiz_page_id').val();
    jQuery('#responseform input[name="nextpage"]').val(page);
}

function pageSubmit(page, type){
    jQuery('#quiz_page_id').val(page);
    jQuery('#responseform input[name="nextpage"]').val(page);
    jQuery('#responseform input[name="next"]').trigger( "click" );
}

function instructionsPopupClose(){
    jQuery('.instructions-popup-box').removeClass('open');
}

function instructionsPopupOpen(id){
    instructionsPopupClose();
    jQuery('#instruction_'+id).addClass('open');
}

function resourcePopupClose(){
    jQuery('.instructions-popup-box').removeClass('open');
}

function resourcePopupOpen(id){
    resourcePopupClose();
    jQuery('#resource_'+id).addClass('open');
}

