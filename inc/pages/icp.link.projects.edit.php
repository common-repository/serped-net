<div class="wrap">    <?php    include 'input.php';    if (isset($_POST['project_name'])) {        if(!wp_verify_nonce( $_POST['_wpnonce'], 'icp-linkpro-852' )){             echo '<script type="text/javascript"> window.location = \'' . admin_url('admin.php?page=icp_link_projects') . '\'; </script>';             die();        }          global $wpdb;        $id = sanitize_text_field($_REQUEST['id']);        if (isset($_POST['project_name']) && !empty($_POST['project_name'])) {            $links = explode("\n", $_POST['links']);            $flag = true;            foreach ($links as $key => $link) {                //$link = trim($link);                $links[$key] = $link = trim($link);                if (!empty($link)) {                    $preg = preg_match('/http:\/\//', $link);                    $preg1 = preg_match('/https:\/\//', $link);                    if ($preg == 0 && $preg1 == 0) {                        $links[$key] = $link = 'http://' . $link;                    };                    $tmp = filter_var($link, FILTER_VALIDATE_URL);                    $flag = ( $tmp ) ? $flag : false;                } else {                    $flag = false;                    unset($links[$key]);                }            };            if ($flag) {                $tableName = $wpdb->prefix . "icp_project_keywords";                $res1 = $wpdb->delete($tableName, array('id_project_fk' => $id), null);                $tableName = $wpdb->prefix . "icp_project_links";                $res2 = $wpdb->delete($tableName, array('id_project_fk' => $id), null);                $table_name = $wpdb->prefix . "icp_projects";                $ignores = (!empty($_POST['ignores'])) ? sanitize_text_field($_POST['ignores']) : '';                $ignores = explode("\n", $ignores);                $tmp = "";                foreach ($ignores as $ig) {                    $ig = trim($ig);                    $tmp .= $ig . ",";                };                $ignores = rtrim($tmp, ",");                $wpdb->update($table_name, array(                    'project_name' => sanitize_text_field($_POST['project_name']),                    'posts' => ( $_POST['hyperlink_posts'] == 'on' ) ? 1 : 0,                    'pages' => ( $_POST['hyperlink_pages'] == 'on' ) ? 1 : 0,                    'existing' => ( $_POST['hyperlink_existing'] == 'on' ) ? 1 : 0,                    'new' => ( $_POST['hyperlink_new'] == 'on' ) ? 1 : 0,                    'comments' => ( $_POST['comments'] == 'on' ) ? 1 : 0,                    'new_window' => ( $_POST['new_window'] == 'on' ) ? 1 : 0,                    'headings' => ( $_POST['headings'] == 'on' ) ? 1 : 0,                    'max_replace' => (!empty($_POST['max_replace'])) ? sanitize_text_field($_POST['max_replace']) : 3,                    'max_keyword' => (!empty($_POST['max_keyword'])) ? sanitize_text_field($_POST['max_keyword']) : 1,                    'no_follow_weight' => (!empty($_POST['no_follow_weight'])) ? trim(sanitize_text_field($_POST['no_follow_weight']), "%") : 0,                    'ignores' => $ignores,                    'created_at' => time()                        ), array('id' => $id), null);                $pId = $id;                $keywords = explode("\n", $_POST['keywords']);                foreach ($keywords as $keyword) {                    $keyword = trim($keyword);                    //$keyword = trim($keyword, "\n");                    if (!empty($keyword)) {                        $wpdb->insert($wpdb->prefix . "icp_project_keywords", array('id_project_fk' => $pId, 'keyword' => sanitize_text_field($keyword)), array('%d', '%s'));                    };                };                foreach ($links as $link) {                    $wpdb->insert($wpdb->prefix . "icp_project_links", array('id_project_fk' => $pId, 'url' => sanitize_text_field($link)), array('%d', '%s'));                };                $wpdb->delete($wpdb->prefix . "icp_keyword_stats", array('project_id' => $pId));#### HYPERLINK EXISTING CONTENT - BEGIN ####                $query = "SELECT * FROM " . $wpdb->prefix . "posts WHERE post_type = 'post' OR post_type = 'page'";                $posts = $wpdb->get_results($query, OBJECT);                $query = "SELECT * FROM " . $wpdb->prefix . "icp_projects WHERE id = " . $pId;                $project = $wpdb->get_row($query);                $exclude = explode(",", $project->ignores);                foreach ($exclude as $key => $ex) {                    $exclude[$key] = trim($ex);                };                foreach ($posts as $post) {                    if (!in_array($post->ID, $exclude) && !in_array($post->post_name, $exclude)) {                        if ($project->existing == 1) {                            $comments = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "comments WHERE comment_post_ID = " . $post->ID);                            if ($project->posts == 1 && $post->post_type == 'post') {                                //                                $res = srpd_content_helper($project, $post->post_content, $post->ID, 'content');                                //                                if ($project->headings == 1)                                    srpd_content_helper($project, $post->post_title, $post->ID, 'title');                                //                                if ($project->comments == 1) {                                    foreach ($comments as $comment) {                                        $res3 = srpd_content_helper($project, $comment->comment_content, $post->ID, 'comment');                                    }                                };                            };                            if ($project->pages == 1 && $post->post_type == 'page') {                                //                                srpd_content_helper($project, $post->post_content, $post->ID, 'content');                                //                                if ($project->headings == 1)                                    srpd_content_helper($project, $post->post_title, $post->ID, 'title');                                //                                if ($project->comments == 1) {                                    foreach ($comments as $comment) {                                        $res3 = srpd_content_helper($project, $comment->comment_content, $post->ID, 'comment');                                    }                                };                            };                        };                    };                };                #### HYPERLINK EXISTING CONTENT - END ####                echo '<div id="message" class="updated"><p>Link Project updated successfully.</p></div>';                $message = urlencode("Link Project updated successfully.");                echo '<script type="text/javascript"> window.location = \'' . admin_url('admin.php?page=icp_link_projects&type=updated&message=' . $message) . '\'; </script>';            die();            } else {                echo '<div id="message" class="error"><p>Please enter valid URLs for the links.</p></div>';            };        } elseif (isset($_POST['project_name']) && empty($_POST['project_name'])) {            echo '<div id="message" class="error"><p>The Project Name is required.</p></div>';        };    };    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete') {        global $wpdb;        $id = sanitize_text_field($_REQUEST['id']);        $tableName = $wpdb->prefix . "icp_projects";        $res = $wpdb->delete($tableName, array('id' => $id), null);        $tableName = $wpdb->prefix . "icp_project_keywords";        $res1 = $wpdb->delete($tableName, array('id_project_fk' => $id), null);        $tableName = $wpdb->prefix . "icp_project_links";        $res2 = $wpdb->delete($tableName, array('id_project_fk' => $id), null);        $tableName = $wpdb->prefix . "icp_keyword_stats";        $res3 = $wpdb->delete($tableName, array('project_id' => $id), null);        if ($res !== false && $res1 !== false && $res2 !== false) {            echo '<div id="message" class="updated"><p>Project deleted successfully.</p></div>';            $message = urlencode("Project deleted successfully.");            echo '<script type="text/javascript"> window.location = \'' . admin_url('admin.php?page=icp_link_projects&type=updated&message=' . $message) . '\'; </script>';            die();        } else {            echo '<div id="message" class="error"><p>There was an error while deleting the project.</p></div>';            $message = urlencode("There was an error while deleting the project.");            echo '<script type="text/javascript"> window.location = \'' . admin_url('admin.php?page=icp_link_projects&type=error&message=' . $message) . '\'; </script>';            die();        };    } elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {        global $wpdb;        $id = sanitize_text_field($_REQUEST['id']);        $tableName = $wpdb->prefix . "icp_projects";        $query = "SELECT * FROM " . $tableName . " WHERE id = " . $id;        $result = $wpdb->get_row($query);        $tableName = $wpdb->prefix . "icp_project_keywords";        $query = "SELECT * FROM " . $tableName . " WHERE id_project_fk = " . $id;        $result1 = $wpdb->get_results($query, OBJECT);        $tableName = $wpdb->prefix . "icp_project_links";        $query = "SELECT * FROM " . $tableName . " WHERE id_project_fk = " . $id;        $result2 = $wpdb->get_results($query, OBJECT);        ?>        <h2>Edit Link Project</h2>        <form method="post" action="" id="edit_form">            <input type="hidden" name="_wpnonce" value="<?=wp_create_nonce('icp-linkpro-852')?>">            <table class="form-table" >                <tr class="form-field" >                    <td width="200px" >                        <label for="project_name" >Project Name:</label>                    </td>                    <td>                        <input type="text" name="project_name" id="project_name" style="width: 50%;height: 30px;" class="" value="<?php echo esc_html($result->project_name); ?>"/>                    </td>                </tr>                <tr>                    <td width="200px" >                        <label for="auto_hyperlink" >Auto-hyperlink:</label>                    </td>                    <td>	                        <input type="checkbox" name="hyperlink_posts" id="hyperlink_posts" <?php if ($result->posts == 1) echo checked; ?>/> Posts                        <input type="checkbox" name="hyperlink_pages" id="hyperlink_pages" style="margin-left: 30px;" <?php if ($result->pages == 1) echo checked; ?>/> Pages                    </td>                </tr>                <tr>                    <td width="200px" >                        <label for="auto_hyperlink" >Content to auto-hyperlink:</label>                    </td>                    <td>	                        <input type="checkbox" name="hyperlink_existing" id="hyperlink_existing" <?php if ($result->existing == 1) echo checked; ?>/> Existing content                        <input type="checkbox" name="hyperlink_new" id="hyperlink_new" style="margin-left: 30px;" <?php if ($result->new == 1) echo checked; ?>/> New content                    </td>                </tr>                <tr>                    <td>                        <label>Replace keywords in Comments</label>                    </td>                    <td>                        <input type="checkbox" name="comments" id="comments" <?php if ($result->comments == 1) echo checked; ?>/>                    </td>                </tr>                <tr>                    <td>                        <label>Open links in new window</label>                    </td>                    <td>                        <input type="checkbox" name="new_window" id="new_window" <?php if ($result->new_window == 1) echo checked; ?>/>                    </td>                </tr>                <tr>                    <td>                        <label>Hyperlink Titles/Headings of posts/pages</label>                    </td>                    <td>                        <input type="checkbox" name="headings" id="headings" <?php if ($result->headings == 1) echo checked; ?>/>                    </td>                </tr>                <tr>                    <td>                        <label>Maximum hyperlink replacements per post/page</label>                    </td>                    <td>                        <select name="max_replace" id="max_replace" style="width:110px;">                                  <option value="1" <?php if ($result->max_replace == 1) echo 'selected="selected"'; ?>>1</option>                            <option value="2" <?php if ($result->max_replace == 2) echo 'selected="selected"'; ?>>2</option>                            <option value="3" <?php if ($result->max_replace == 3) echo 'selected="selected"'; ?>>3</option>                            <option value="4" <?php if ($result->max_replace == 4) echo 'selected="selected"'; ?>>4</option>                            <option value="5" <?php if ($result->max_replace == 5) echo 'selected="selected"'; ?>>5</option>                           </select>                    </td>                </tr>                <tr>                    <td>Maximum hyperlink replacements per keyword on this site (sitewide)</td>                    <td>                        <select name="max_keyword" id="max_keyword" style="width:110px;">                                  <option value="1" <?php if ($result->max_keyword == 1) echo 'selected="selected"'; ?>>1</option>                            <option value="2" <?php if ($result->max_keyword == 2) echo 'selected="selected"'; ?>>2</option>                            <option value="3" <?php if ($result->max_keyword == 3) echo 'selected="selected"'; ?>>3</option>                            <option value="4" <?php if ($result->max_keyword == 4) echo 'selected="selected"'; ?>>4</option>                            <option value="5" <?php if ($result->max_keyword == 5) echo 'selected="selected"'; ?>>5</option>                               <option value="6" <?php if ($result->max_keyword == 6) echo 'selected="selected"'; ?>>6</option>                            <option value="7" <?php if ($result->max_keyword == 7) echo 'selected="selected"'; ?>>7</option>                            <option value="8" <?php if ($result->max_keyword == 8) echo 'selected="selected"'; ?>>8</option>                            <option value="9" <?php if ($result->max_keyword == 9) echo 'selected="selected"'; ?>>9</option>                            <option value="10" <?php if ($result->max_keyword == 10) echo 'selected="selected"'; ?>>10</option>                               <option value="11" <?php if ($result->max_keyword == 11) echo 'selected="selected"'; ?>>11</option>                            <option value="12" <?php if ($result->max_keyword == 12) echo 'selected="selected"'; ?>>12</option>                            <option value="13" <?php if ($result->max_keyword == 13) echo 'selected="selected"'; ?>>13</option>                            <option value="14" <?php if ($result->max_keyword == 14) echo 'selected="selected"'; ?>>14</option>                            <option value="15" <?php if ($result->max_keyword == 15) echo 'selected="selected"'; ?>>15</option>                               <option value="16" <?php if ($result->max_keyword == 16) echo 'selected="selected"'; ?>>16</option>                            <option value="17" <?php if ($result->max_keyword == 17) echo 'selected="selected"'; ?>>17</option>                            <option value="18" <?php if ($result->max_keyword == 18) echo 'selected="selected"'; ?>>18</option>                            <option value="19" <?php if ($result->max_keyword == 19) echo 'selected="selected"'; ?>>19</option>                            <option value="20" <?php if ($result->max_keyword == 20) echo 'selected="selected"'; ?>>20</option>                           </select>                     </td>                </tr>                <tr>                    <td>Weight of no-follow links on this site (sitewide)</td>                    <td>                        <select name="no_follow_weight" id="no_follow_weight" style="width:110px;">                                  <option value="0" <?php if ($result->no_follow_weight == 0) echo 'selected="selected"'; ?>>0</option>                            <option value="10" <?php if ($result->no_follow_weight == 10) echo 'selected="selected"'; ?>>10</option>                            <option value="20" <?php if ($result->no_follow_weight == 20) echo 'selected="selected"'; ?>>20</option>                            <option value="25" <?php if ($result->no_follow_weight == 25) echo 'selected="selected"'; ?>>25</option>                            <option value="30" <?php if ($result->no_follow_weight == 30) echo 'selected="selected"'; ?>>30</option>                            <option value="40" <?php if ($result->no_follow_weight == 40) echo 'selected="selected"'; ?>>40</option>                            <option value="50" <?php if ($result->no_follow_weight == 50) echo 'selected="selected"'; ?>>50</option>                            <option value="60" <?php if ($result->no_follow_weight == 60) echo 'selected="selected"'; ?>>60</option>                            <option value="70" <?php if ($result->no_follow_weight == 70) echo 'selected="selected"'; ?>>70</option>                            <option value="75" <?php if ($result->no_follow_weight == 75) echo 'selected="selected"'; ?>>75</option>                            <option value="80" <?php if ($result->no_follow_weight == 80) echo 'selected="selected"'; ?>>80</option>                            <option value="90" <?php if ($result->no_follow_weight == 90) echo 'selected="selected"'; ?>>90</option>                            <option value="100" <?php if ($result->no_follow_weight == 100) echo 'selected="selected"'; ?>>100</option>                        </select> %                     </td>                </tr>                <tr>                    <td>                        <label>Ignore Pages and Posts (add each Post/Page id or post name on a new line)</label>                    </td>                    <td>                        <textarea rows="3" cols="20" name="ignores" id="ignores" style="width: 50%"><?php                            $ign = explode(",", $result->ignores);                            $ign = implode("\n", $ign);                            echo $ign;                            ?></textarea>                    </td>                </tr>                <tr>                    <td>                        <label>Keywords (add each keyword on a new line)</label>                    </td>                    <?php                    $keywords = "";                    foreach ($result1 as $keyword) {                        $keywords .= esc_html($keyword->keyword) . "\n";                    }                    ?>                    <td>                        <textarea id="keywords" name="keywords" rows="5" cols="20" style="width: 50%"><?php echo $keywords; ?></textarea>                    </td>                </tr>                <tr>                    <td>                        <label>Links (add each link on a new line)</label>                    </td>                    <?php                    $links = "";                    foreach ($result2 as $link) {                        $links .= esc_url($link->url) . "\n";                    }                    ?>                    <td>                        <textarea id="links" name="links" rows="5" cols="40" style="width: 50%"><?php echo $links; ?></textarea>                    </td>                </tr>                <tr>                    <td colspan="2" >                        <p class="submit" >                            <input type="submit" name="submit" value="Save Link Project" /> &nbsp;or&nbsp; <a href="<?php echo admin_url('admin.php?page=icp_link_projects'); ?>" >Cancel</a>                        </p>                    </td>                </tr>            </table>        </form>        <?php    };    ?></div>