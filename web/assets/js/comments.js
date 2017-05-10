/**
 * Created by Albertpv on 10/05/17.
 */


// twig image.comments array must be accessible

$(document).ready(function() {

    const START_COMMENTS  = 3;

    function requestMoreComments(callback) {

        $.ajax({
            url: '/image-more-comments/' + totalImages[comment].comments.length + START_COMMENTS,
            type: 'post',
            success: function (response) {

                callback(JSON.parse(response));
            }
        });
        return false;
    }

    function onMoreCommentsResponse(data) {

        for (var index in data) {

            console.log("image: " + data[index]);
            totalImages[comment].push(data[index]);
        }

        console.log("total images: " + totalImages);
    }


    $("#icon_load_more_comments").unbind('click').click(function (event) {

        console.log("event");

        var myJSVar = $('#image-data').data('var');
        console.dir("VAR: " + myJSVar);

        console.dir("image: " + imageView);

        console.log("ID: " + imageView.id);
        console.log("ID2: " + imageView['id']);

        event.preventDefault();

        //requestMoreComments(onMoreCommentsResponse);
    });



});