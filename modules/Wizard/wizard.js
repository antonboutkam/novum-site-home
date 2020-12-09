$('.install-btn').each(function(i, e){

    $(e).on('click', function(){
        const package = $(e).data('package');

        // alert("Run this command: \"./composer require " + package + " && ./novum db:make ")
        // $(e).removeClass('btn-success').addClass('btn-default').html('<i class="fas fa-cog fa-spin"></i> INSTALLING');

        const data = {
            '_do' : 'InstallPackage',
            'package' : package
        }
        $.post(window.location, data, function(data){
            console.log(data);
        }, 'json');
    });



})
