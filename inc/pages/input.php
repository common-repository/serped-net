<?phpif ( !defined( 'ABSPATH' ) ) exit; if (isset($access))    $user = srpd_check_access(1);else    $user = srpd_check_access();wp_enqueue_style( 'style',  srpd_root_path() ."/css/icp_styles.css", 'all');?>