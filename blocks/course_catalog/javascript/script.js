$(document).ready(function(){ 
    jQuery("#catalog_search").keyup(function(e){
        catalogLoadCourses(0);
    });
});

function toggleView(){
    jQuery(".courseactionsbox .action-view i").toggleClass("active");
    if (jQuery(".block_course_catalog .tab-content").hasClass('list-view')){
        jQuery(".block_course_catalog .tab-content").removeClass("list-view");
        jQuery(".block_course_catalog .tab-content").addClass("grid-view");
        var view = 'grid';
    } else {
        jQuery(".block_course_catalog .tab-content").addClass("list-view");
        jQuery(".block_course_catalog .tab-content").removeClass("grid-view");
        var view = 'list';
        
    }
    catalogSetSettings('catalog-view-type', view, false);
}

function toggleFilter(){
    jQuery(".form-filter .coursesortbox").removeClass("active");
    jQuery(".form-filter .coursfilterbox").toggleClass("active");
}

function toggleSort(){
    jQuery(".form-filter .coursesortbox").toggleClass("active");
    jQuery(".form-filter .coursfilterbox").removeClass("active");
}

function catalogSortCourses(type, dnav){
    jQuery('.coursesortbox span').removeClass('active');
    if (jQuery('.coursesortbox .'+type+' .fa-sort-asc').hasClass('active')){
        jQuery('.coursesortbox .fa').removeClass('active');
        jQuery('.coursesortbox .'+type+' .fa-sort-desc').addClass('active');
        var nav = 'DESC';
    } else {
        jQuery('.coursesortbox .fa').removeClass('active');
        jQuery('.coursesortbox .'+type+' .fa-sort-asc').addClass('active');
        var nav = 'ASC';
    }
    jQuery('.coursesortbox .'+type).addClass('active');
    nav = (dnav) ? dnav : nav;
    
    catalogSetSettings('catalog-sort-field', type, false);
    catalogSetSettings('catalog-sort-nav', nav, true);
}

function catalogFilter(type){
    var value = jQuery('#catalog_sort_'+type).val();
    if (value){
        catalogSetSettings('catalog-filter-'+type, value, true);
    } else {
        catalogSetSettings('catalog-filter-'+type, '', true);
    }
}

function catalogSetSettings(name, value, update){
    var url = jQuery('#catalog_coursesearch').attr('action');
    jQuery.get( url+'?action=set_user_preferences&name='+name+'&value='+value, function( data ) {
        if (update){
            catalogLoadCourses(0);       
        }
    });
}

function catalogLoadCourses(page){
    var url = jQuery('#catalog_coursesearch').attr('action');
    var search = jQuery("#catalog_search").val();
    if (search.length > 0){
        search = search.replace(' ', '__');
    }
    
    if (jQuery("#frontpage-course-enroll-list").length){
        $("#frontpage-course-enroll-list .courses").addClass('loader').html('<i class="fa fa-spin fa-spinner"></i>').load( url+'?action=catalog-load-courses&page='+page+'&search='+search, function(e) {
            $("#frontpage-course-enroll-list .courses").removeClass('loader');
        });
    }
}

function catalogLoadMore(page, type){
    var url = jQuery('#catalog_coursesearch').attr('action');
    var search = jQuery("#catalog_search").val();
    if (search.length > 0){
        search = search.replace(' ', '__');
    }
    
    if (jQuery("#frontpage-course-enroll-list").length){
        $("#frontpage-course-enroll-list .courses .load-more-box").remove();
        jQuery("#frontpage-course-enroll-list .courses").addClass('loader').append('<div class="fa-loader"><i class="fa fa-spin fa-spinner"></i></div>');

        jQuery.ajax({ type: "GET",   
             url: url+'?action=catalog-load-courses&page='+page+'&search='+search,   
             success : function(html){
                 $("#frontpage-course-enroll-list .courses").append(html);
                 $("#frontpage-course-enroll-list .courses").removeClass('loader');
                 $("#frontpage-course-enroll-list .courses .fa-loader").remove();
             }
        });
    }
}
