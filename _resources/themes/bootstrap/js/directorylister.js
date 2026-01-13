$(document).ready(function() {

    // Get page-content original position
    var contentTop = $('#page-content').offset().top;

    // Show/hide top link on page load
    showHideTopLink(contentTop);

    // Show/hide top link on scroll
    $(window).scroll(function() {
        showHideTopLink(contentTop);
    });

    // Scroll page on click action
    $('#page-top-link').click(function() {
        $('html, body').animate({ scrollTop: 0 }, 'fast');
        return false;
    });

    // Hash button on click action
    $('.file-info-button').click(function(event) {

        // Get the file name and path
        var name = $(this).closest('li').attr('data-name');
        var path = $(this).closest('li').attr('data-href');

        // Set modal title value
        $('#file-info-modal .modal-title').text(name);

        $('#file-info .md5-hash').text('Loading...');
        $('#file-info .sha1-hash').text('Loading...');
        $('#file-info .filesize').text('Loading...');

        $.ajax({
            url:      '?hash=' + encodeURIComponent(path),
            type:     'get',
            dataType: 'json',
            success:  function(obj) {

                // Set modal pop-up hash values
                if (obj && typeof obj === 'object') {
                    $('#file-info .md5-hash').text(obj.md5 || 'N/A');
                    $('#file-info .sha1-hash').text(obj.sha1 || 'N/A');
                    $('#file-info .filesize').text(obj.size !== null ? obj.size : 'N/A');
                } else {
                    $('#file-info .md5-hash').text('N/A');
                    $('#file-info .sha1-hash').text('N/A');
                    $('#file-info .filesize').text('N/A');
                }

            }
        });

        // Show the modal
        $('#file-info-modal').modal('show');

        // Prevent default link action
        event.preventDefault();

    });

});

function showHideTopLink(elTop) {
    if($('#page-navbar').offset().top + $('#page-navbar').height() >= elTop) {
        $('#page-top-nav').show();
    } else {
        $('#page-top-nav').hide();
    }
}
