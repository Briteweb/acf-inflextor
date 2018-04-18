<?php

/*
Plugin Name: ACF Inflextor - Flexible Content Inspector
Plugin URI: https://briteweb.com
Description: Shows you which pages each layout in a Flexible Content field is used on
Version: 1.0.0
Author: Briteweb
Author URI: http://briteweb.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists('ACF_Inflextor') ) {

  class ACF_Inflextor {

  	public function __construct() {

      //if ( ! class_exists( 'ACF' ) ) return;

      add_action( 'admin_menu', function() {

        if ( ! class_exists( 'ACF' ) ) return;

        add_submenu_page(
          'edit.php?post_type=acf-field-group',
          'Inflextor',
          'Inflextor',
          'manage_options',
          'inflextor',
          [ $this, 'admin_page' ]
        );
      }, 100);

  	}

    public function get_fields() {

      $all_groups = acf_get_field_groups();
      $fields = [];

      foreach ( $all_groups as $group ) {
        $group_fields = acf_get_fields( $group );

        foreach ( $group_fields as $field ) {
          if ( $field['type'] == 'flexible_content' ) {
            $fields[] = [
              'field' => $field,
              'group' => $group
            ];
          }
        }
      }

      //$this->pre($fields);

      return $fields;

    }

		public function get_layouts() {
			$key = $_GET['key'];

			$field = acf_get_field( $key );
			//$this->pre($field);

			$field_name = $field['name'];

			$all_posts = get_posts( [
				'post_type' => 'any',
				'posts_per_page' => -1,
				'orderby' => 'post_title',
				'order' => 'asc',
				'meta_key' => $field_name
			] );

			$posts = [];
			foreach ( $all_posts as $post ) {
				$fields = get_field( $field_name, $post->ID );
				if ( empty( $fields ) ) continue;
				$post_layouts = [];

				foreach ( $fields as $f ) {
					$post_layouts[] = $f['acf_fc_layout'];
				}

				if ( empty( $post_layouts ) ) continue;

				$post->layouts = $post_layouts;
				$posts[] = $post;

			}

			$layouts = [];

			foreach ( $field['layouts'] as $layout ) {
				$layout_posts = [];

				foreach ( $posts as $post ) {
					if ( in_array( $layout['name'], $post->layouts ) ) $layout_posts[] = $post;
				}

				$layouts[] = [
					'key' => $layout['key'],
					'name' => $layout['name'],
					'label' => $layout['label'],
					'posts' => $layout_posts
				];
			}
			//$this->pre($layouts);

			return $layouts;

		}

		public function list_posts() {

			$key = $_GET['key'];
			$field = acf_get_field( $key );

			$layouts = $this->get_layouts();
			//$this->pre($layouts);

      ?>
      <div class="wrap acf-settings-wrap">
        <h1>ACF Inflextor</h1>

				<h3>Showing all layouts for <?php echo $field['label']; ?></h3>

				<?php foreach( $layouts as $layout ) : ?>
        <div class="acf-box">
          <div class="title">
            <h3><?php echo $layout['label']; ?></h3>
          </div>

          <div class="inner">
            <table class="wp-list-table widefat fixed striped">

              <thead>
                <th>Post</th>
                <th>Type</th>
              </thead>

    	         <tbody>
								 <?php foreach ( $layout['posts'] as $post ) : ?>
									<tr>
										<td><a href=""><strong><?php echo $post->post_title; ?></strong></a></td>
										<td><?php echo $post->post_type; ?></td>
									</tr>
								 <?php endforeach; ?>
               </tbody>

             </table>
          </div>
        </div>
				<?php endforeach; ?>

      </div>
      <?php
    }

    public function list_layouts() {
      $fields = $this->get_fields();

      ?>
      <div class="wrap acf-settings-wrap">
        <h1>ACF Inflextor</h1>

        <div class="acf-box">
          <div class="title">
            <h3>Flexible Content Layouts</h3>
          </div>

          <div class="inner">
            <table class="wp-list-table widefat fixed striped">

              <thead>
                <th>Field</th>
                <th>Group</th>
              </thead>

    	         <tbody>
                 <?php foreach ( $fields as $field ) :  ?>
                 <tr>
                   <td><a href="<?php echo admin_url( 'edit.php?post_type=acf-field-group&page=inflextor&key=' . $field['field']['key'] ); ?>"><strong><?php echo $field['field']['label']; ?></strong></a></td>
                   <td><a href=""><?php echo $field['group']['title']; ?></a></td>
                 </tr>
                <?php endforeach; ?>
               </tbody>

             </table>
          </div>
        </div>

      </div>
      <?php
    }

		public function admin_page() {
			if ( isset( $_GET['key'] ) && !empty( $_GET['key'] ) ) {
				$this->list_posts();
			} else {
				$this->list_layouts();
			}
		}

    public function pre( $arr ) {
      echo '<pre>';
      var_dump( $arr );
      echo '</pre>';
    }

  }

  new ACF_Inflextor();

}
