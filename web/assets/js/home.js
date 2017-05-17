/**
 * Created by Albertpv on 27/04/17.
 */


/**
 * This script is used to manage the pagination of the home view of the application.
 * Each time the button to load more images is clicked, this script launches an AJAX request
 * to obtain 5 more photos from the database.
 *
 * If there are not more photos to loaded, the script also removes the button to load more photos.
 */
$(document).ready(function() {

    $('.dropdown-toggle').dropdown();

    var imagesLoaded = 5;

    const IMAGES_PER_PAGE  = 5;

    function requestMoreImages(callback) {

        $.ajax({
            url: '/home-more-images/' + imagesLoaded,
            type: 'post',
            success: function (response) {

                callback(JSON.parse(response));
            }
        });
        return false;
    }

    function renderMoreCommentsButton(image) {
        var res = "";

        if (image.numComments <= 3) return res;

        var href = "load_comments_" + image.id;

        res += '<div id=' + href + '>';
        res +=  '<!-- Used to know which image is clicked to load more comments -->'
                + '<a id="icon_load_more_comments" class="btn glyphicon glyphicon-plus" data-var=' + image.id + '></a>'
                + '</div>';

        return res;
    }

    function renderCommentArea(logged, image) {
        var res = "";

        if (!logged) return res;

        var href= "/new-comment/" + image.id;

        res = '<form accept-charset="UTF-8" action=' + href + '>';

        res += '<textarea class="form-control textarea-comment" id="text" name="text" placeholder="Type in your comment" rows="2">'
        res += '</textarea>';
        res += '<button class="btn btn-info" type="submit">Publicar comentario</button>'
        res += '</form>';

        return res;
    }

    function renderComments(comments) {

        var res = "";

        comments = JSON.parse(comments);

        for (var i = 0; i < comments.length; i++) {

            var img = new Image("http://grup17.com/assets/img/profile_img/"+ comments[i].fkUser +".jpg");

            res += '<p class="comment vlimit"> <strong class="name-image">';
            res += '<img class="img-circle" src="http://grup17.com/assets/img/profile_img/'+ comments[i].fkUser +'.jpg" height="20" width="20" >';
            //else res += '<img class="img-circle" src="http://grup17.com/assets/img/profile_img/img_profile_default.jpg" height="20" width="20" >';

            res += comments[i].userName + " :";
            res += '</strong><small> ' + comments[i].content + '</small></p>';
        }

        return res;
    }


    function renderLikeOption(logged, liked, image) {
        var res = "";

        if (!logged) return res;

        res += "<p>";

        var href = "/like/" + image.id;

        if (!liked)
            res += '<a href=' + href + ' class=" glyphicon glyphicon-heart-empty button-like" role="button"></a>';

        else
            res += '<a href=' + href + ' class=" glyphicon glyphicon-heart button-like " role="button"></a>';

        res += "</p>";

        return res;
    }


    /**
     * Renders the new images arrived from the database.
     *
     * @param logged    Boolean value to know if the user is or not logged.
     * @param images    The array of images to load.
     * @param comments  An array of comments made on the image.
     * @param dates     The time formatted of when the photo was uploaded.
     */
    function renderNewImages(logged, images, comments, dates) {

        for (var i = 0; i < images.length; i++) {

            var element = images[i];
            var date    = dates[i];
            var comment = comments[i];

            var hrefImageView = "/image-view/" + element.id;
            var hrefUserProfile = "/user-profile/" + element.fkUser + "/1";


            var imgAsset = "assets/img/upload_img/" + element.id + '_400x300.jpg';

            var commentId = "comments_list_" + element.id;

            $('#img-container').append(

                '<div class=" col-sm-6 col-md-4"> <div class="thumbnail limit-image"> ' +
                '<a  href=' + hrefImageView + '><h2>' + element.title + '</h2></a>'
                + '<img  src=' + imgAsset + ' height="400" width="300" >'
                + '<div class="caption"> <div class="caption">'
                + '<a  href=' + hrefUserProfile + '><h4>' + element.userName + '</h4></a>'
                + '</div> <p>'
                + '<span class="intro-text conj-span">'
                + '<span>' + element.visits + '</span>'
                + '<span class="glyphicon glyphicon-eye-open glyphicon-black" ></span>'
                + '</span> <span class="intro-text conj-span">'
                + '<span>'  + element.likes + '</span>'
                + '<span class="glyphicon glyphicon-thumbs-up glyphicon-black" ></span>'
                + '</span> <span class="intro-text conj-span">'
                + '<span>' + element.numComments + '</span>'
                + '<span class="glyphicon glyphicon-comment glyphicon-black" ></span>'
                + '</span> </p> <div class="">'

                + '<div id=' + commentId + '>'

                + renderComments(comment)

                + '</div>'

                + renderMoreCommentsButton(element)

                + renderCommentArea(logged, element)

                + renderLikeOption(logged, element.liked, element)

                + '</div> </div> <div class="card-footer text-muted">'
                + '<p class="card-text"><small class="text-muted">' + date + '</small></p>'
                + '</div> </div> </div>'
            );
        }

    }

    /**
     * Parses the response of the AJAX request an calls the render method described above.
     * Also, if there are not more photos to load, removes the button to load more photos.
     *
     * @param data  All the json data.
     */
    function onMoreImagesResponse(data) {

        // I know its already parsed. But magically it do not work without a second parse
        var logged      = JSON.parse(data['logged']);
        var images      = JSON.parse(data['images']);
        var comments    = JSON.parse(data['comments']);
        var dates       = JSON.parse(data['dates']);
        var loaded      = parseInt(JSON.parse(data['loaded']));
        var total       = parseInt(JSON.parse(data['total_public_images']));


        renderNewImages(logged, images, comments, dates);

        // all images have been loaded, so we remove the pagination button
        if (loaded + imagesLoaded === total || loaded < IMAGES_PER_PAGE) {

            $("#icon_load_more").remove();
        }

        imagesLoaded += loaded;
    }




   $("#icon_load_more").unbind('click').click(function (event) {

       event.preventDefault();

       requestMoreImages(onMoreImagesResponse);
   });

});