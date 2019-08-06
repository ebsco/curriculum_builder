<style>
select, input, button {
  font-size: small;
}
table {
  background-color: #999999;
}
tr:nth-child(even) {
    background-color: #CCCCCC;
}
tr:nth-child(odd) {
    background-color: #FFFFFF;
}
td {
  padding: 5px;
}
</style>
<?php

  $clean = strip_tags_deep($_POST);

  if (isset($clean['month']) && isset($clean['year']) && isset($clean['day'])) {
     $clean['expires'] = date('Y-m-d H:i:s',strtotime($clean['month'].'/'.$clean['day'].'/'.$clean['year']));
  }
  
  if (isset($clean['user']) && isset($clean['pass'])) {
    if (($clean['user'] == $username) && ($clean['pass'] == $password)) {
        setcookie("manage_access_login","1", time()+1800);
        $loggedin = 1;
        ?>
        <div class="readingListLink">
          <h3>Successfully logged in.</h3>
        </div>                        
        <?php
    } else {
        $failed_login = 1;
    }
  }
  if (isset($clean['logout'])) {
    setcookie("manage_access_login", "0", 1);
    $loggedout = 1;
  }
  
  if (((isset($_COOKIE['manage_access_login'])) || isset($loggedin)) && (!(isset($loggedout)))) {
 
        if (isset($clean['action'])) {
            if ($clean['action'] == "Set New Expiration") {
                $sql = "UPDATE oauth SET expires = '".$clean['expires']."' WHERE id = " . $clean['id'] . ";";
                mysqli_query($c,$sql);
                ?>
            <div class="readingListLink">
              <h3 style="color:green;"><strong>Successfully updated expiration date for <?php echo $clean['consumerkey']; ?></strong></h3>
            </div>                                
                <?php
            }else if ($clean['action'] == "Delete") {
                $sql = "DELETE FROM oauth WHERE id = " . $clean['id'] . ";";
                mysqli_query($c,$sql);
                ?>
            <div class="readingListLink">
              <h3 style="color:red;"><strong>Successfully deleted <?php echo $clean['consumerkey']; ?></strong></h3>
            </div>                
                <?php
            } else if ($clean['action'] == "Create New Key Pair") {
                if ((strlen($clean['consumerkey']) <= 0) || (strlen($clean['secret']) <= 0) || (strlen($clean['expires']) <= 0) || (strlen($clean['expires']) <= 0) || !(isset($clean['consumerkey'])) || !(isset($clean['secret']))) {
                    ?>
                    <div class="readingListLink">
                      <h3 style="color:red;">You cannot have an empty Consumer Key or Password, and you must set an expiration date.  Please try again.</h3>
                    </div>
                    <?php
                } else {
                    $sql = "SELECT id FROM oauth WHERE oauth_consumer_key = '".$clean['consumerkey']."';";
                    $results = mysqli_query($c,$sql);
                    if (mysqli_num_rows($results) > 0) {
                    ?>
                    <div class="readingListLink">
                      <h3 style="color:red;">Consumer key <?php echo $clean['consumerkey']; ?> already exists. Please delete the existing one first before creating a new one.</h3>
                    </div>
                    <?php
                    
                    } else {
                    $sql = "INSERT INTO oauth (oauth_consumer_key,secret,expires) VALUES ('".$clean['consumerkey']."','".$clean['secret']."','".$clean['expires']."');";
                    mysqli_query($c,$sql);
                    ?>
                        <div class="readingListLink">
                          <h3 style="color:green;"><strong>Successfully inserted consumer key: <?php echo $clean['consumerkey']; ?></strong></h3>
                        </div>                                    
                    <?php
                    }
                }
            } else if ($clean['action'] == "reassign") {
              $sql = "UPDATE lists SET oauth_consumer_key = '".$clean['toKey']."' WHERE oauth_consumer_key = '".$clean['fromKey']."';";
              mysqli_query($c,$sql);
              ?>
                        <div class="readingListLink">
                          <h3 style="color:green;"><strong>Successfully reassigned lists from consumer key <?php echo $clean['fromKey']; ?> to <?php echo $clean['toKey']; ?></strong></h3>
                        </div>   
              <?php
            } else if ($clean['action'] == "relink") {
              $sql = "UPDATE lists SET oauth_consumer_key = '".$clean['consumerKey']."' WHERE id = '".$clean['listid']."';";
              mysqli_query($c,$sql);
              ?>
                        <div class="readingListLink">
                          <h3 style="color:green;"><strong>Successfully reassigned list <?php echo $clean['listid']; ?> to consumer key <?php echo $clean['consumerKey']; ?> </strong></h3>
                        </div>   
              <?php
        
            } else {
                ?>
            <div class="readingListLink">
              <h3>Unrecognized Actions.  Try again.</h3>
            </div>                
                <?php
            }
        }
        ?>
            <div class="readingListLink">
              <form action="manageaccess.php" method="post">
                <input type="submit" name="logout" value="Log Out" />
              </form>
            </div>

            <div class="readingListLink">
              <form action="manageaccess.php" method="post">
                <h3>Create New Access Key Pair</h3>
                <table>
                  <tr>
                    <th>Consumer Key</th>
                    <th>Secret</th>
                    <th>Expiration Date</th>
                    <th></th>
                  </tr>
                  <td>
                    <input type="text" name="consumerkey" placeholder="Username" />
                  </td>
                  <td>
                    <input type="text" name="secret" placeholder="Password" />
                  </td>
                  <td>
                            <select name="month">
                              <?php
                              for ($i = 1; $i <= 12; $i++) {
                                echo "<option value='" . $i . "'";
                                if ($i == date("n")) {
                                  echo " selected";
                                }
                                echo ">" . date("F", mktime(0,0,0,$i,10)) . "</option>"; 
                              }
                              ?>
                            </select>
                            <select name="day">
                            <?php for ($i = 1; $i <= 31; $i++) {
                              echo "<option value='" . $i . "'";
                              if ($i == date("j")) {
                                echo " selected";
                              }
                              echo ">".$i."</option>";
                            } ?>
                            </select>
                            <select name="year">
                              <?php
                                 $startyear = date("Y");
                                 $endyear = $startyear + 10;
                                 for ($i = $startyear; $i <= $endyear; $i++) {
                                    echo "<option value='" . $i . "'";
                                    if ($i == (date("Y")+1)) {
                                      echo " selected";
                                    }
                                    echo ">" . $i . "</option>";                                    
                                 }
                              ?>
                            </select>
                  </td>
                  <td>
                    <input type="submit" name="action" value="Create New Key Pair" />
                  </td>
                </table>
              </form>
            </div>

            <div class="readingListLink">
                <h3>Edit or Delete Existing Access Key Pair</h3>
                <p style="color:red;"><em>Warning:</em> Removing a Consumer Key / Secret pairing or setting an incorrect expiration date will deny access to the reading list to any site using that key pair.</p>
                <table>
                    <tr>
                        <th>Consumer Key</th>
                        <th>Secret</th>
                        <th>Expiration</th>
                        <th></th>
                    </tr>
                    <?php
                      $sql = "SELECT id, oauth_consumer_key, secret, expires FROM oauth ORDER BY oauth_consumer_key;";
                      $results = mysqli_query($c,$sql);
                      while ($row = mysqli_fetch_array($results)) {
                        $expiration = getdate(strtotime($row['expires']));
                        ?>
                        <tr>
                        <form action="manageaccess.php" method="post">
                            <td><?php echo $row['oauth_consumer_key']; ?></td>
                            <td><?php echo $row['secret']; ?></td>
                            <td><input type="hidden" name="id" value="<?php echo $row['id']; ?>" /><input type="hidden" name="consumerkey" value="<?php echo $row['oauth_consumer_key']; ?>" />
                            <select name="month">
                              <?php
                              for ($i = 1; $i <= 12; $i++) {
                                echo "<option value='" . $i . "'";
                                if ($i == $expiration['mon']) {
                                  echo " selected";
                                }
                                echo ">" . date("F", mktime(0,0,0,$i,10)) . "</option>"; 
                              }
                              ?>
                            </select>
                            <select name="day">
                            <?php for ($i = 1; $i <= 31; $i++) {
                              echo "<option value='" . $i . "'";
                              if ($i == $expiration['mday']) {
                                echo " selected";
                              }
                              echo ">".$i."</option>";
                            } ?>
                            </select>
                            <select name="year">
                              <?php
                                 $startyear = min((integer)date("Y"),$expiration['year']);
                                 $endyear = date("Y") + 10;
                                 for ($i = $startyear; $i <= $endyear; $i++) {
                                    echo "<option value='" . $i . "'";
                                    if ($i == $expiration['year']) {
                                      echo " selected";
                                    }
                                    echo ">" . $i . "</option>";                                    
                                 }
                              ?>
                            </select>
                            </td>
                            <td>
                              <a target="_blank" href="admin.php?autologin=<?php echo $row['oauth_consumer_key'].".....".$row['secret']; ?>">Configure / Admin</a>
                            </td>
                            <td>
                              <input type="submit" name="action" style="color:white; background-color:green;" value="Set New Expiration" />
                              <input type="submit" name="action" value="Delete" style="margin-left: 50px; color:white; background-color:red;" onclick="return confirm('Are you sure you want to delete this key <?php echo $row['oauth_consumer_key']; ?> ?');" /></td>
                        </tr></form>
                        <?php
                      }
                    ?>
                </table>
            </div>

            
            <div class="readingListLink">
              <form action="manageaccess.php" method="post">
                <input type="submit" name="logout" value="Log Out" />
              </form>
            </div>  
            <div class="lseONLY">
            <h1>LSE ONLY</h1>
            <form action="manageaccess.php" method="post" onsubmit="return confirm('Are you sure you want to re-assign lists?  This can\'t be undone.  LSE ONLY!');">
                <input type="hidden" name="action" value="reassign" />Reassign lists from Consumer Key <input type="text" name="fromKey" placeholder="FROM" /> to Consumer Key <input type="text" name="toKey" placeholder="TO" /> <input type="submit" /> 
            </form>
            <form action="manageaccess.php" method="post" onsubmit="return confirm('Are you sure you want to re-assign lists?  This can\'t be undone.  LSE ONLY!');">
                <input type="hidden" name="action" value="relink" />Link a list to a consumer key:  ListID: <input type="text" name="listid" placeholder="LIST ID" /> | Consumer Key: <input type="text" name="consumerKey" placeholder="Consumer Key" /> <input type="submit" /> 
            </form>
            </div>          
        <?php
  } else {
    if ((isset($clean['logout']) || isset($loggedout))) {
        ?>
            <div class="readingListLink">
              <h3>You have been logged out.</h3>
            </div>        
        <?php
    }
    if (isset($failed_login)) {
        ?>
            <div class="readingListLink">
              <h3>Your login credentials are incorrect.  Please try again.</h3>
            </div>        
        <?php
    }
    ?>
    <div class="readingListLink">
      <h3>Manage Access to the Reading List Tool</h3>
      <form action="manageaccess.php" method="post">
        <p><strong>Username: </strong><input type="text" name="user" placeholder="User Name" /></p>
        <p><strong>Password: </strong><input type="password" name="pass" /></p>
        <p><input type="submit" value="Log In" /></p>
      </form>
    </div>        
    <?php
  }
?>