<div id="toptabcontent" class="clearfix" >
    <div class="loginform">
        <h2 style="font-size: ">Login</h2>
        <?php if($fail == 'y'){ ?>
        <div class="loginfailed">Invalid login -- pleasae try again</div>
        <?php } ?>
<form action="auth.php" method="post">
    <table>
        <tr><td><label style="font-size: 80%"><b>Username:</b> </label></td><td><input tyep="text" name="userId" value="" /></td></tr>
        <tr><td><label style="font-size: 80%"><b>Password:</b> </label></td><td><input type="password" name="password" value="" /></td></tr>       
        <tr><td></td><td><input type="submit" value="Login" /></td></tr>
        <?php if($path == "PDF"){ ?>
        <tr>
            <td><input type="hidden" value="<?php echo $path?>" name="path"/></td>
            <td><input type="hidden" value="<?php echo $db ?>" name ="db" /></td>
            <td><input type="hidden" value="<?php echo $an ?>" name="an" /></td>
        </tr>
        <?php } ?>
        <?php if($path == "results"){ ?>
        <tr>
            <td><input type="hidden" value="<?php echo $path?>" name="path"/></td>
            <td><input type="hidden" value="<?php echo $query ?>" name ="query" /></td>
            <td><input type="hidden" value="<?php echo $fieldCode ?>" name ="fieldcode" /></td>
        </tr>
        <?php } ?>
          <?php if($path == "record"||$path=="HTML"){ ?>
        <tr>
            <td><input type="hidden" value="<?php echo $path?>" name="path"/></td>
            <td><input type="hidden" value="<?php echo $db ?>" name ="db" /></td>
            <td><input type="hidden" value="<?php echo $an ?>" name="an" /></td>
            <td><input type="hidden" value="<?php echo $highlight?>" name="highlight"/></td>
            <td><input type="hidden" value="<?php echo $resultId ?>" name="resultId" /></td>
            <td><input type="hidden" value="<?php echo $recordCount ?>" name="recordCount" /></td>
            <td><input type="hidden" value="<?php echo $query ?>" name="query" /></td>
            <td><input type="hidden" value="<?php echo $fieldCode ?>" name="fieldcode" /></td>
        </tr>
        <?php } ?>
    </table>
</form>
    </div>
    
</div>

