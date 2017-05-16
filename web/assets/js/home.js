/**
 * Created by Albertpv on 27/04/17.
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

            res += '<p class="comment"> <strong class="name-image">';
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


    function renderNewImages(logged, images, comments, dates) {

        for (var i = 0; i < images.length; i++) {

            var element = images[i];
            var date    = dates[i];
            var comment = comments[i];

            var hrefImageView = "/image-view/" + element.id;
            var hrefUserProfile = "/user-profile/" + element.fkUser + "/1";


            // TODO: no se hace con {{ asset etc }}, preguntar
            var imgAsset = "assets/img/upload_img/" + element.id + '_400x300.jpg';

            var commentId = "comments_list_" + element.id;

            $('#img-container').append(

                '<div class=" col-sm-6 col-md-4"> <div class="thumbnail"> ' +
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



        //console.log("IMAGES HERE: " + imagesLoaded + " and loaded: " + loaded);
    }




   $("#icon_load_more").unbind('click').click(function (event) {

       event.preventDefault();

       requestMoreImages(onMoreImagesResponse);
   });

});