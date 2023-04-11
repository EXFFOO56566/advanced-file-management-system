<?php
/********************************************************************************
 * #                      Advanced File Manager v3.0
 * #******************************************************************************
 * #      Author:     Convergine.com
 * #      Email:      info@convergine.com
 * #      Website:    http://www.convergine.com
 * #
 * #
 * #      Version:    3.0
 * #      Copyright:  (c) 2009 - 2015 - Convergine.com
 * #
 * #*******************************************************************************/
session_start();
require_once("includes/dbconnect.php"); //Load the settings
require_once("includes/functions.php"); //Load the functions
$msg = "";
$upload_dir_id = (!empty($_REQUEST["upload_dir"])) ? strip_tags(str_replace("'", "`", $_REQUEST["upload_dir"])) : '';
$upload_dir = getFolderPathById($upload_dir_id);
//print $upload_dir_path;
$_SESSION['uploadFolder'] = $upload_dir;

$disable_button = false;
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] != true) {
    header("Location: index.php");
    exit();
} else {
    //get access level
    if (isset($_SESSION["accesslevel"])) {
        $access = $_SESSION["accesslevel"];
        //determin admin or not.
        if (stristr($access, "abcdef")) {
            $level = "admin";
        } else {
            $level = "user";
        }
    } else {
        header("Location: index.php");
    }

    if ($level != "user") {
        $upload_dirs = "";
        $query       = "SELECT * FROM {$db_pr}folders ORDER BY name ASC";
        $result = mysqli_query($mysqli,$query) or die("error getting folders from database");
        while ($rr = mysqli_fetch_assoc($result)) {
            $upload_dirs .= "<option value='" . $rr["name"] . "' " . ($upload_dir == $rr["name"] ? "selected" : "") . ">" . $rr["name"] . "</option>";
        }
    } else {
        if (!stristr($access, "i")) { //if user cant see all files
            $q   = "SELECT upload_dirs FROM {$db_pr}users WHERE id='" . $_SESSION["idUser"] . "'";
            $res = mysqli_query($mysqli,$q);
            $rr  = mysqli_fetch_assoc($res);
            if (!empty($rr["upload_dirs"])) {
                $folders = str_replace(',', "','", $rr["upload_dirs"]);
                $query   = "SELECT * FROM {$db_pr}folders WHERE id IN ('" . $folders . "') ORDER BY name ASC";
                $result = mysqli_query($mysqli,$query) or die("error getting folders from database");
                while ($rr = mysqli_fetch_assoc($result)) {
                    $upload_dirs .= "<option value='" . $rr["name"] . "' " . ($upload_dir == $rr["id"] ? "selected" : "") . ">" . $rr["name"] . "</option>";
                }
            } else {
                $disable_button = true;
            }
        } else {
            $query = "SELECT * FROM {$db_pr}folders ORDER BY name ASC";
            $result = mysqli_query($mysqli,$query) or die("error getting folders from database");
            while ($rr = mysqli_fetch_assoc($result)) {
                $upload_dirs .= "<option value='" . $rr["name"] . "' " . ($upload_dir == $rr["id"] ? "selected" : "") . ">" . $rr["name"] . "</option>";
            }
        }

    }
    $sSQL = "SELECT id,username,filesize,quota,extensions FROM {$db_pr}users WHERE id='" . $_SESSION["idUser"] . "'";
    $result = mysqli_query($mysqli,$sSQL) or die("err: " . mysqli_error($mysqli) . $sSQL);
    if ($row = mysqli_fetch_assoc($result)) {
        foreach ($row as $key => $value) {
            $$key = $value;
        }
    }
    $_extensions = implode("|", explode(",", $extensions));
    mysqli_free_result($result);
    include "includes/header.php";
    ?>

    <link rel="stylesheet" href="includes/uploader/css/jquery.fileupload.css">
    <link rel="stylesheet" href="includes/uploader/css/blueimp-gallery.min.css">


    <div id="content-main">
    <h2>Upload File</h2>

    <h3>Uploading to: <strong><?php echo($upload_dir) ?></strong></h3>
    <ul class="uploadInfo">
        <li>
            <label>Your total upload limit:</label><?php echo $quota; ?> MB
        </li>
        <li>
            <label>Your maximum file size:</label><?php echo $filesize; ?> MB
        </li>
        <li>
            <label>Allowed extensions:</label><span><?php echo join(", ",explode(",",$extensions))?></span>
        </li>
    </ul>

    <div class="clear"> <br/>

    <div id="respText"></div>

    <?php if ($disable_button) {
        echo "No folders assigned to your account!";
    } else {
        ?>



        <!-- The file upload form used as target for the file upload widget -->
        <form id="fileupload" action="<?php echo($script_dir) ?>includes/server/index.php" method="POST"
              enctype="multipart/form-data">
            <!-- Redirect browsers with JavaScript disabled to the origin page -->
            <noscript><input type="hidden" name="redirect" value="index.php">
            </noscript>
            <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
            <div class="row fileupload-buttonbar">
                <div class="col-lg-7">
                    <!-- The fileinput-button span is used to style the file input field as button -->
                <span class="btn btn-success fileinput-button">
                    <i class="glyphicon glyphicon-plus"></i>
                    <span>Add files...</span>
                    <input type="file" name="files[]" multiple>
                </span>
                    <button type="submit" class="btn btn-primary start">
                        <i class="glyphicon glyphicon-upload"></i>
                        <span>Start upload</span>
                    </button>
                    <button type="reset" class="btn btn-warning cancel">
                        <i class="glyphicon glyphicon-ban-circle"></i>
                        <span>Cancel upload</span>
                    </button>
                    <!-- The global file processing state -->
                    <span class="fileupload-process"></span>
                </div>
                <!-- The global progress state -->
                <div class="col-lg-5 fileupload-progress fade">
                    <!-- The global progress bar -->
                    <div class="progress progress-striped active" role="progressbar" aria-valuemin="0"
                         aria-valuemax="100">
                        <div class="progress-bar progress-bar-success" style="width:0%;"></div>
                    </div>
                    <!-- The extended global progress state -->
                    <div class="progress-extended">&nbsp;</div>
                </div>
            </div>
            <!-- The table listing the files available for upload/download -->
            <table role="presentation" class="table table-striped">
                <tbody class="files">
                <tr style="display: none">
                    <td></td>
                </tr>
                </tbody>
            </table>
        </form>
        <br>
        <br>

        <div class="clear"></div>
        <br/><br/>
        <h2>Switch Folder</h2>
        <div class="clear"></div>
        <form action="upload_step2.php" enctype="multipart/form-data" method="get" name="ff2"
              class="form-horizontal popup-form">
            <div class="form-group">
                <div class="col-sm-8">
                    <select name="upload_dir" id="upload_dir" class="form-control">
                        <?php getFoldersDrop($upload_dir_id)?>
                    </select>
                </div>
                <div class="col-sm-4">
                    <button type="submit" class="btn btn-primary btn-block">Switch</button>
                </div>
            </div>
            <br/><br/>
        </form>
        <div data-filter=":even" class="blueimp-gallery blueimp-gallery-controls" id="blueimp-gallery">
            <div class="slides"></div>
            <h3 class="title"></h3>
            <a class="prev">‹</a>
            <a class="next">›</a>
            <a class="close">×</a>
            <a class="play-pause"></a>
            <ol class="indicator"></ol>
        </div>
        <!-- The template to display files available for upload -->
        <script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td>
            <span class="preview"></span>
        </td>
        <td>
            <p class="name">{%=file.name%}</p>
            <strong class="error text-danger"></strong>
        </td>
        <td>
            <p class="size">Processing...</p>
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
        </td>
        <td>
            {% if (!i && !o.options.autoUpload) { %}
                <button class="btn btn-primary start" disabled>
                    <i class="glyphicon glyphicon-upload"></i>
                    <span>Start</span>
                </button>
            {% } %}
            {% if (!i) { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Cancel</span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}


        </script>
        <!-- The template to display files available for download -->
        <script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        <td>
            <span class="preview">
                {% if (file.thumbnailUrl) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
                {% } %}
            </span>
        </td>
        <td>
            <p class="name">
                {% if (file.url) { %}
                    <?php  ?><a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                {% } else { %}
                    <span>{%=file.name%}</span>
                {% } %}
            </p>
            {% if (file.error) { %}
                <div><span class="label label-danger">Error</span> {%=file.error%}</div>
            {% } %}
        </td>
        <td>
            <p class="size">{%=o.formatFileSize(file.size)%}</p>
        </td>
        <td>
            {% if (file.deleteUrl) { %}

            {% } else { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Cancel</span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}


        </script>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
        <script src="includes/uploader/js/vendor/jquery.ui.widget.js"></script>
        <!-- The Templates plugin is included to render the upload/download listings -->
        <script src="includes/uploader/js/git/tmpl.min.js"></script>
        <!-- The Load Image plugin is included for the preview images and image resizing functionality -->
        <script src="includes/uploader/js/git/load-image.min.js"></script>
        <!-- The Canvas to Blob plugin is included for image resizing functionality -->
        <script src="includes/uploader/js/git/canvas-to-blob.min.js"></script>
        <!-- Bootstrap JS is not required, but included for the responsive demo navigation -->
        <script src="includes/uploader/js/git/bootstrap.min.js"></script>
        <!-- blueimp Gallery script -->
        <script src="includes/uploader/js/git/jquery.blueimp-gallery.min.js"></script>
        <!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
        <script src="includes/uploader/js/jquery.iframe-transport.js"></script>
        <!-- The basic File Upload plugin -->
        <script src="includes/uploader/js/jquery.fileupload.js"></script>
        <!-- The File Upload processing plugin -->
        <script src="includes/uploader/js/jquery.fileupload-process.js"></script>
        <!-- The File Upload image preview & resize plugin -->
        <script src="includes/uploader/js/jquery.fileupload-image.js"></script>
        <!-- The File Upload audio preview plugin -->
        <script src="includes/uploader/js/jquery.fileupload-audio.js"></script>
        <!-- The File Upload video preview plugin -->
        <script src="includes/uploader/js/jquery.fileupload-video.js"></script>
        <!-- The File Upload validation plugin -->
        <script src="includes/uploader/js/jquery.fileupload-validate.js"></script>
        <!-- The File Upload user interface plugin -->
        <script src="includes/uploader/js/jquery.fileupload-ui.js"></script>
        <!-- The main application script -->
        <script src="includes/uploader/js/main.js"></script>
        <!-- The XDomainRequest Transport is included for cross-domain file deletion for IE 8 and IE 9 -->
        <!--[if (gte IE 8)&(lt IE 10)]>
        <script src="includes/uploader/js/cors/jquery.xdr-transport.js"></script><![endif]-->
        <script>
            /*jslint unparam: true, regexp: true */
            /*global window, $ */
            $(function () {
                'use strict';
                // Change this to the location of your server-side upload handler:
                var url = 'includes/server/',
                    uploadButton = $('<button/>')
                        .addClass('btn btn-primary')
                        .prop('disabled', true)
                        .text('Processing...')
                        .on('click', function () {
                            var $this = $(this),
                                data = $this.data();
                            $this
                                .off('click')
                                .text('Abort')
                                .on('click', function () {
                                    $this.remove();
                                    data.abort();
                                });
                            data.submit().always(function () {
                                $this.remove();
                            });
                        });
                $('#fileupload').fileupload({
                    url: url,
                    dataType: 'json',
                    autoUpload: false,
                    acceptFileTypes: /(\.|\/)(<?php echo $_extensions?>)$/i,
                    maxFileSize: <?php echo empty($filesize)?0:$filesize*1024*1024?>, // 5 MB
                    // Enable image resizing, except for Android and Opera,
                    // which actually support image resizing, but fail to
                    // send Blob objects via XHR requests:
                    disableImageResize: /Android(?!.*Chrome)|Opera/
                        .test(window.navigator.userAgent),
                    previewMaxWidth: 80,
                    previewMaxHeight: 80,
                    previewCrop: true
                }).on('fileuploadadd',function (e, data) {
                    data.context = $('<div/>').addClass("fileInfo").appendTo('#files');
                    $.each(data.files, function (index, file) {
                        var node = $('<p/>')
                            .append($('<span/>').text(file.name));
                        if (!index) {
                            node
                                .append('<br>')
                                .append(uploadButton.clone(true).data(data));
                        }
                        node.appendTo(data.context);
                    });
                }).on('fileuploadprocessalways',function (e, data) {
                    var index = data.index,
                        file = data.files[index],
                        node = $(data.context.children()[index]);
                    if (file.preview) {
                        node
                            .prepend('<br>')
                            .prepend(file.preview);
                    }
                    if (file.error) {
                        node
                            .append('<br>')
                            .append($('<span class="text-danger"/>').text(file.error));
                        node.parent().addClass('error')
                    }
                    if (index + 1 === data.files.length) {
                        data.context.find('button.start')
                            .text('Upload')
                            .prop('disabled', !!data.files.error);
                        data.context.find('button.cancel')
                            .text('Remove')
                            .prop('disabled', !!data.files.error);
                    }
                }).on('fileuploadprogressall',function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $('#progress .progress-bar').css(
                        'width',
                        progress + '%'
                    );
                }).on('fileuploaddone',function (e, data) {
                    $.each(data.result.files, function (index, file) {
                        if (file.url) {
                            var link = $('<a>')
                                .attr('target', '_blank')
                                .prop('href', file.url);
                            $(data.context.children()[index])
                                .wrap(link).parent().parent().addClass('ook');//.find('span').append("<br><span style='color:green'>Uploaded</span>");
                        } else if (file.error) {
                            var error = $('<span class="text-danger"/>').text(file.error);
                            $(data.context.children()[index])
                                .append('<br>')
                                .append(error);
                            $(data.context.children()[index]).parent().addClass('error')
                        }
                    });
                }).on('fileuploadfail',function (e, data) {
                    $.each(data.files, function (index, file) {
                        var error = $('<span class="text-danger"/>').text('File upload failed.');
                        $(data.context.children()[index])
                            .append('<br>')
                            .append(error).parent().parent().addClass('error');
                    });
                }).prop('disabled', !$.support.fileInput)
                    .parent().addClass($.support.fileInput ? undefined : 'disabled');
            });
            var disableFile = true
        </script>

    <?php } ?>
    </div>
    </div>
    </div>
    </div>
    </div>
    <?php include "includes/footer.php"; ?>
<?php } ?>