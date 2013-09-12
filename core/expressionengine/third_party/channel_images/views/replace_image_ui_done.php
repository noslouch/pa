<p style="text-align: center">
Image Replacement done!<br>
You can now close this popup.<br>
</p>


<script type="text/javascript">
if (window.parent){
    try {
        var d = new Date();
        window.parent.jQuery.colorbox.close();
        window.parent.jQuery('.CIField').find('.ImgUrl img').each(function(i, elem){
            window.parent.jQuery(elem).attr('src', window.parent.jQuery(elem).attr('src')+'&'+d.getTime() );
        });
    }
    catch(errorThrown) {

    }
}
</script>
