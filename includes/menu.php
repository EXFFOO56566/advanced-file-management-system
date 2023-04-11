<div class="clear"></div>
<div id="content">

    <div id="content-navbar" >

<?php if($level=="admin"){ ?>
    <div class="bs-glyphicons">
        <ul class="bs-glyphicons-list">
            <li>
                <a href="upload_step1.php" class="<?php if(strpos($_SERVER['SCRIPT_NAME'],'upload_step1.php')!==false ||strpos($_SERVER['SCRIPT_NAME'],'upload_step2.php')!==false  ){echo "active";}?>">
                    <span class="glyphicon glyphicon-cloud-upload"></span>
                    <span class="glyphicon-class">upload</span>
                </a>
            </li>
            <li>
                <a href="files.php" class="<?php if(strpos($_SERVER['SCRIPT_NAME'],'files.php')!==false  ){echo "active";}?>">
                    <span class="glyphicon glyphicon-floppy-disk"></span>
                    <span class="glyphicon-class">files</span>
                </a>
            </li>
            <li>
                <a href="users.php" class="<?php if(strpos($_SERVER['SCRIPT_NAME'],'users.php')!==false  ){echo "active";}?>">
                    <span class="glyphicon glyphicon-user"></span>
                    <span class="glyphicon-class">users</span>
                </a>
            </li>
            <li>
                <a href="folders.php" class="<?php if(strpos($_SERVER['SCRIPT_NAME'],'folders.php')!==false  ){echo "active";}?>">
                    <span class="glyphicon glyphicon-folder-open"></span>
                    <span class="glyphicon-class">folders</span>
                </a>
            </li>
            <li>
                <a href="edit_messages.php" class="<?php if(strpos($_SERVER['SCRIPT_NAME'],'edit_messages.php')!==false  ){echo "active";}?>">
                    <span class="glyphicon glyphicon-comment"></span>
                    <span class="glyphicon-class">messages</span>
                </a>
            </li>
            <li>
                <a href="extensions.php" class="<?php if(strpos($_SERVER['SCRIPT_NAME'],'extensions.php')!==false  ){echo "active";}?>">
                    <span class="glyphicon glyphicon-tags"></span>
                    <span class="glyphicon-class">extensions</span>
                </a>
            </li>

            <li>
                <a href="stats.php" class="<?php if(strpos($_SERVER['SCRIPT_NAME'],'stats.php')!==false  ){echo "active";}?>">
                    <span class="glyphicon glyphicon-stats"></span>
                    <span class="glyphicon-class">statistics</span>
                </a>
            </li>
            <li>
                <a href="logs.php" class="<?php if(strpos($_SERVER['SCRIPT_NAME'],'logs.php')!==false  ){echo "active";}?>">
                    <span class="glyphicon glyphicon-list-alt"></span>
                    <span class="glyphicon-class">logs</span>
                </a>
            </li>

            <li >
                <a href="settings.php" class="<?php if(strpos($_SERVER['SCRIPT_NAME'],'settings.php')!==false  ){echo "active";}?>">
                    <span class="glyphicon glyphicon-cog"></span>
                    <span class="glyphicon-class">settings</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="clear"></div>

  <?php  } else { ?>
        <div class="bs-glyphicons">
            <ul class="bs-glyphicons-list">
                <?php  if(stristr($access,"g")){ ?>
                <?php
                //check if multiple folders assigned
                $q="SELECT upload_dirs FROM {$db_pr}users WHERE id='".$_SESSION["idUser"]."'";
                $res=mysqli_query($mysqli,$q);
                $r=mysqli_fetch_assoc($res);
                $tmp = explode(",",$r["upload_dirs"]);
                $link = "upload_step1.php";
                ?>
                    <li>
                        <a href="<?php echo $link?>"  class="<?php if(strpos($_SERVER['SCRIPT_NAME'],'upload_step1.php')!==false ||strpos($_SERVER['SCRIPT_NAME'],'upload_step2.php')!==false ){echo "active";}?>">
                            <span class="glyphicon glyphicon-cloud-upload"></span>
                            <span class="glyphicon-class">upload</span>
                        </a>
                    </li>
                <?php }?>
                <?php if(stristr($access, "t") ||stristr($access, "i")||stristr($access, "g")){?>
                <li>
                    <a href="files.php" class="<?php if(strpos($_SERVER['SCRIPT_NAME'],'files.php')!==false  ){echo "active";}?>">
                        <span class="glyphicon glyphicon-floppy-disk"></span>
                        <span class="glyphicon-class">files</span>
                    </a>
                </li>
                <?php }?>
                <li>
                    <a href="stats_user.php" class="<?php if(strpos($_SERVER['SCRIPT_NAME'],'stats_user.php')!==false  ){echo "active";}?>">
                        <span class="glyphicon glyphicon-stats"></span>
                        <span class="glyphicon-class">statistics</span>
                    </a>
                </li>
            </ul>
        </div>
<?php }?>

    <div class="bottom_line">
        <div class="content-center">
        </div>
    </div>
       
    <div class="clear"></div>
    </div>
<script>
    $(function(){
        if($("#table_previous").length){
        $("#table_previous").prepend("<span class='glyphicon glyphicon-chevron-left'></span>")
        }
        if($("#table_next").length){
            $("#table_next").append("<span class='glyphicon glyphicon-chevron-right'></span>")
        }
    })
</script>