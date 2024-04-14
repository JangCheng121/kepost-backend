<?php 
    $sql = 'select count(*) as count from login_urls where url="'.$_SERVER['REMOTE_ADDR'].'"';    
    $exist = $this->db->query($sql)->row()->count;
    if($exist == 0)  redirect(base_url(), 'refresh');
?>
<div class="sidebar-menu">
    <header class="logo-env" >

        <!-- logo -->
        <div class="logo" style="">
            <a href="<?php echo base_url(); ?>">
                <img src="<?php echo base_url()?>uploads/logo.png"  style="max-height:60px;"/>
            </a>
        </div>

        <!-- logo collapse icon -->
        <div class="sidebar-collapse" style="">
            <a href="#" class="sidebar-collapse-icon with-animation">

                <i class="entypo-menu"></i>
            </a>
        </div>

        <!-- open/close menu icon (do not remove if you want to enable menu on mobile devices) -->
        <div class="sidebar-mobile-menu visible-xs">
            <a href="#" class="with-animation">
                <i class="entypo-menu"></i>
            </a>
        </div>
    </header>

    <div style=""></div>    
    <ul id="main-menu" class="">
        <!-- add class "multiple-expanded" to allow multiple submenus to open -->
        <!-- class "auto-inherit-active-class" will automatically add "active" class for parent elements who are marked already with class "active" -->


        <!-- PHONE LIST -->
        <li class="<?php if ($page_name == 'list') echo 'active'; ?> ">
            <a href="<?php echo base_url(); ?>list">
                <i class="entypo-mobile"></i>
                <span><?php echo ('Phone List'); ?></span>
            </a>
        </li>
        
        <!-- SEND SMS -->
        <li class="<?php if ($page_name == 'sendsms') echo 'active'; ?> ">
            <a href="<?php echo base_url(); ?>sendsms">
                <i class="entypo-mail"></i>
                <span><?php echo ('Send SMS'); ?></span>
            </a>
        </li>
        

        <!-- ACCOUNT -->
        <li class="<?php if ($page_name == 'manage_profile') echo 'active'; ?> ">
            <a href="<?php echo base_url(); ?>profile">
                <i class="entypo-lock"></i>
                <span><?php echo ('Account'); ?></span>
            </a>
        </li>

    </ul>

</div>

