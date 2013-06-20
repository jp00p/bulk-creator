jQuery(document).ready(function(){

    jQuery("#add-pages").click(function(){ add_pages(); });

});
    
var pageid = 0;
    
function add_pages() {
   
   if(jQuery("#titles").val() == '') return false;
   
   jQuery(".update-site").css("visibility", "visible");
    
    // implement left-trim
    String.prototype.ltrim = function() {
    	return this.replace(/^\s+/,"");
    }
    
    if(jQuery('#titles').val().match(/,/)) {
    
        // get and clean up page titles
        var pageNames = jQuery('#titles').val().split(",");
        
        jQuery(pageNames).each(function(i){
            pageNames[i]=this.ltrim();
        });
        
        for(ipnames=0;ipnames<pageNames.length;ipnames++){
        
            jQuery('#titles').val(pageNames[ipnames]);
            add_pages();
            
        }
        
        return false;
    
    } 
    
    
    // get page parent ID (if set)
    var parent = jQuery('#page_id').val();
    
    if(parent == '' || !parent) {
    
        parent = -1;
        
        jQuery('.page-list .wrap > ul').append('<li class="page-item-new' + pageid + '">' + jQuery('#titles').val() + ' <a href="JavaScript:del_page(' + pageid + ');">Remove</a></li>');
    
        jQuery('#page_id').append('<option value="new' + pageid + '">' + jQuery('#titles').val() + '</option>');
    
    } else {
        
        if(jQuery("#page_id").val()) {
        
            var parentname = jQuery('#page_id option[value=' + parent + ']').html();
            
        } else {
        
            var parentname = '';	                    
            
        }
        
        var parentspace = '&nbsp;&nbsp;&nbsp;';
        
        if(parentname.match(/&nbsp;/g)){
        
            var nums = parentname.match(/&nbsp;/g).length;
            
            for(inums=0;inums<nums;inums++){
                parentspace += '&nbsp;';
            }
            
        }
        
        jQuery('.page-list .wrap > ul li.page-item-' + parent).append('<li class="page-item-new' + pageid + '">' + jQuery('#titles').val() + ' <a href="JavaScript:del_page(' + pageid + ');">Remove</a></li>');
        
        jQuery('#page_id option[value=' + parent + ']').after('<option class="p_' + parent + '" value="new' + pageid + '">' + parentspace + jQuery('#titles').val() + '</option>');
        
    
    }
    
    
    jQuery('#pages-list').val(jQuery('#pages-list').val() + pageid + '|' + parent + '|' + jQuery('#titles').val() + '\n');
    
    pageid++;
    
    jQuery('#titles').val('');
    
    jQuery('#page_id').attr('selectedIndex', 0);
    
    console.log(jQuery("#pages-list").val());
    
}
    
    
    
function del_page(pageid){

    jQuery('li.page-item-new' + pageid).remove();
    
    jQuery('#page_id option[value=new' + pageid + ']').remove();
    
    jQuery('#page_id option.p_new' + pageid).remove();
    
    var maintext = jQuery('#pages-list').val();
    
    //remove the page
    maintext = maintext.replace(new RegExp(pageid + '\\|[^\\n]*\\n', "i"), "");
    
    //remove the children
    maintext = maintext.replace(new RegExp('\\d*\\|new' + pageid + '\\|[^\\n]*\\n', "i"), "");
    
    //jQuery('#titles').val(maintext);
    
}