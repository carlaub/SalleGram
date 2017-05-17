/**
 * Created by Albertpv on 10/05/17.
 */


// twig image.comments array must be accessible

$(document).ready(function() {
    $('.dropdown-toggle').dropdown();


    const START_COMMENTS  = 3;


    /**
     * A hashed array that stores the number of comments loaded
     * for each image.
     *
     * @type {Array}
     */
    var commentsCount     = [];


    function requestMoreComments(imageId, lastComment, callback) {

        // because if lastComment is zero, we want to start loading from the third comment cause the first three surely are already loaded
        if (lastComment === 0) lastComment = START_COMMENTS;

        $.ajax({
            url: '/image-more-comments/' + imageId + '/' + lastComment,
            type: 'post',
            success: function (response) {

                callback(JSON.parse(response));
            }
        });
        return false;
    }

    function renderComments(comments) {

        var res = "";

        comments = JSON.parse(comments);

        for (var i = 0; i < comments.length; i++) {

            var file = file;

            var img = new Image();

            url = "http://grup17.com/assets/img/profile_img/"+ comments[i].fkUser +".jpg";

            img.src = url;

            //alert(JSON.stringify(comments[i], null, 4));
            res += '<p class="limit">';

            res += '<img class="img-circle" src="http://grup17.com/assets/img/profile_img/'+comments[i].fkUser+'.jpg" height="20" width="20" >';

            res += '<strong class="name-image">';
            res += comments[i].userName + " : ";
            res += '</strong><small>' + comments[i].content + '</small></p>';
        }

        return res;
    }

    function onMoreCommentsResponse(data) {

        var comments = data['comments'];
        var loaded   = parseInt(JSON.parse(data['loaded']));
        var imageId  = parseInt(JSON.parse(data['image']));
        var total    = parseInt(JSON.parse(data['total_image_comments']));

        if (imageId === -1) return;

        $('#comments_list_' + imageId).append(

            renderComments(comments)
        );

        updateCommentsCount(imageId, loaded);


        // if there are not more possible comments, we remove the + button
        if (commentsCount[imageId] === total) {

            var id = 'load_comments_' + imageId;

            var node = document.getElementById(id);
            node.remove();
        }
    }

    function updateCommentsCount(id, count) {

        if (id in commentsCount) commentsCount[id] += count;

        else {

            commentsCount.push(id);
            commentsCount[id] = count + START_COMMENTS;
        }
    }

    function recoverLastCommentImage(id) {

        if (id in commentsCount) return commentsCount[id];

        return 0;
    }


    // $("#icon_load_more_comments").unbind('click').on('click', function (event) {
    $(document).unbind('click').on('click', '#icon_load_more_comments', function (event) {

        var imageId = $(event.target).data('var'); // gets the image id

        event.preventDefault();

        var lastComment = recoverLastCommentImage(imageId);

        requestMoreComments(imageId, lastComment, onMoreCommentsResponse);
    });

});