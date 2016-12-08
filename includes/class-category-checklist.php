<?php
require_once ABSPATH . '/wp-admin/includes/template.php';

class Adjust_Admin_Category_Checklist extends Walker_Category_Checklist {

    function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {

        extract( $args );

        if ( empty( $taxonomy ) )
            $taxonomy = 'category';

        if ( $taxonomy == 'category' )
            $name = 'post_category';
        else
            $name = 'tax_input['.$taxonomy.']';

        $class = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';

        $current_output = "\n<li id='{$taxonomy}-{$category->term_id}'$class>" . '<label class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="'.$name.'[]" id="in-'.$taxonomy.'-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters( 'the_category', $category->name ) ) . '</label>';

        $output .= $this->filter_output( $current_output, $category, $taxonomy, $li_class, $name );
    }

    function filter_output( $output, $category, $taxonomy, $li_class, $input_name ) {
        return $output;
    }
}

class Notop_Category_Checklist extends Adjust_Admin_Category_Checklist {
	
	function category_has_children( $term_id = 0, $taxonomy = 'category' ) {
		$children = get_categories( array( 'child_of' => $term_id, 'taxonomy' => $taxonomy ) );
		return ( $children );
	}

    function filter_output( $output, $category, $taxonomy, $li_class, $input_name ) {
		if( $category->parent == 0 && $this->category_has_children( $category->term_id, $taxonomy ) ) {
            return "\n<li id='{$taxonomy}-{$category->term_id}'$class>" . esc_html( apply_filters( 'the_category', $category->name ) ) ;
        }
        return $output;
    }
}

class Radio_Category_Checklist extends Adjust_Admin_Category_Checklist {
    function filter_output( $output, $category, $taxonomy, $li_class, $input_name ) {
        return preg_replace('/type="checkbox"/', 'type="radio"', $output);
    }
}

class Complex_Category_Checklist extends Adjust_Admin_Category_Checklist {
    private $walkers;
    function __construct() {
        $this->walkers = [];
    }
    function push($walker) {
        $this->walkers[] = $walker;
    }
    function filter_output( $output, $category, $taxonomy, $li_class, $input_name ) {
        foreach ($this->walkers as $walker) {
            $output = $walker->filter_output( $output, $category, $taxonomy, $li_class, $input_name );
        }
        return $output;
    }
}