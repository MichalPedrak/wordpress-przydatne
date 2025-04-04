
function moreThenOne_admin_add_order_new_column_header( $columns ) {

    $new_columns = array();

    foreach ( $columns as $column_name => $column_info ) {

        $new_columns[ $column_name ] = $column_info;

        if ( 'order_status' === $column_name ) {
            $new_columns['moreThenOne'] = __( 'Szt.', 'rbit-orders-primawera' );
        }
       
        
    }

    return $new_columns;
}
add_filter( 'manage_edit-shop_order_columns', 'moreThenOne_admin_add_order_new_column_header', 120);

add_action( 'manage_shop_order_posts_custom_column', 'moreThenOne_add_wc_order_admin_list_column_content' );

function moreThenOne_add_wc_order_admin_list_column_content( $column ) {

    global $post;

    if ( 'moreThenOne' === $column ) {

        $order = wc_get_order( $post->ID );
        $more = $order->get_meta('moreThenOne', true);
        if( $more != ''){
            echo $more;

        } else {
            moreThenOne_check_order_have_primawera_product($post->ID);
        }
    }
}



function moreThenOne_check_order_have_primawera_product($order_id){
    $order = wc_get_order( $order_id );
    $order_items = $order->get_items();

    $more = '';
    foreach ($order_items as $item) {
        if( $item->is_type( 'line_item' ) ) {

            if($item['quantity'] > 1){
                $more .= '<span class="moreThenOne-item">' . $item['quantity'] . '</span>';
            }
        }
    }
    
    if($more != ''){
      
        echo $more;
        $order->update_meta_data( 'moreThenOne', $more );
        $order->save();
    }
}




// Dodatkowa kolumna dla tabeli zamówień dla próbek perfum niszowych

add_filter( 'manage_edit-shop_order_columns', 'niche_admin_add_order_new_column_header', 120);

add_action( 'manage_shop_order_posts_custom_column', 'niche_add_wc_order_admin_list_column_content' );

function niche_admin_add_order_new_column_header( $columns ) {

    $new_columns = array();

    foreach ( $columns as $column_name => $column_info ) {

        $new_columns[ $column_name ] = $column_info;

        if ( 'moreThenOne' === $column_name ) {
            $new_columns['niche'] = __( 'Próbki nisz.', 'woocommerce' );
        }
       
        
    }

    return $new_columns;
}



function niche_add_wc_order_admin_list_column_content( $column ) {

    global $post;

    if ( 'niche' === $column ) {
        $order = wc_get_order( $post->ID );
        $niche = $order->get_meta('niche', true);


        if(!$niche || !is_numeric($niche)) general_log('Błąd przy obliczanie próbek perfum niszowych');

        if(intval($niche > 0)){
            echo '<span class="niche-item">' . $niche . '</span>';
        } else if(intval($niche) !== 0) {
            echo niche_check_order_have_niche_product($post->ID);
        } 
    }
}


function niche_check_order_have_niche_product($order_id){
    $order = wc_get_order( $order_id );
    $order_items = $order->get_items();

    $all_niche = 0;
    $category_niche = array(36,1636,1637,1638,1639);
    $category_samples = array(1641, 1642, 1643, 1644);

    

    foreach ($order_items as $product) {
        if( $product->is_type( 'line_item' ) ) {

            $product_id = $product->get_product_id();

            $categories = wp_get_post_terms( $product_id, 'product_cat' );

            $is_niche = false; 
            $is_sample = false; 

            $niche = "";
            foreach ( $categories as $category ) {

                if (in_array($category->term_id, $category_niche)) {
                    $is_niche = true;
                }
                if (in_array($category->term_id, $category_samples)) {
                    $is_sample = true;
                }
                if ($is_niche && $is_sample) {
                    break;
                }
            }

            if ($is_niche && $is_sample) {
                $all_niche += $product->get_quantity();
            }

        }
    }
    if($all_niche > 0) {
        $niche .= '<span class="niche-item">' . $all_niche . '</span>';
        echo $niche;
    };

    if($all_niche > 0){
        $order->update_meta_data( 'niche', $all_niche );
        $order->save();
    } else {
        $order->update_meta_data( 'niche', 0 );
        $order->save();
    }
}
