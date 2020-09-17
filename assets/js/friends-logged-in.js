/* global ssi_modal */
// модалка запроса в друзья
function frnd_offer( e ) {
    var th = jQuery( e );
    th.addClass( 'rcl-bttn__loading' );

    rcl_ajax( {
        data: {
            action: 'frnd_send_offer',
            frnd_datas: th.data( 'frnd_request' )
        },
        success: function( result ) {
            var ssiOptions = {
                className: 'frnd_modal frnd_modal_offer',
                sizeClass: 'dialog',
                title: 'Добавить в друзья',
                content: result.content
            };

            ssi_modal.show( ssiOptions );

            th.removeClass( 'rcl-bttn__loading' );
        }
    } );

    return false;
}
// принять/отклонить и т.д.
function frnd_operations( e ) {
    var th = jQuery( e );
    th.addClass( 'rcl-bttn__loading' );

    rcl_ajax( {
        data: {
            action: 'frnd_send_operations',
            frnd_datas: th.data( 'frnd_data' )
        },
        success: function( result ) {
            if ( result['status'] === 'ok' ) {
                rcl_notice( result['content'], 'success', 5000 );
                setTimeout( function() {
                    location.reload( true );
                }, 1000 );
            } else {
                rcl_notice( 'Ошибка. Попробуйте позже', 'error', 5000 );
            }

            th.removeClass( 'rcl-bttn__loading' );
        }
    } );

    return false;
}

// по клику в ЛК на кнопу Запросы в друзья - лоадер в ней
rcl_add_action('rcl_init','frnd_bttn_inc');
function frnd_bttn_inc(){
    jQuery( 'body' ).on( 'click', '.frnd_incoming', function() {
        jQuery( this ).addClass( 'rcl-bttn__loading' );
    });
    jQuery( 'body' ).on( 'click', '.frnd_link', function() {
        rcl_preloader_show( jQuery( this ), 30);
    });
}

// загрузилось - уберем активные классы кнопок
rcl_add_action('rcl_upload_tab','frnd_bttn_inc_reset');
function frnd_bttn_inc_reset(e){
    var tab = e.result.post.tab_id;
    var bttn = jQuery( '.frnd_incoming' );
    
    if(tab === 'friends'){
        jQuery( '#lk-menu .recall-button.active' ).removeClass( 'active' );
        
        bttn.removeClass( 'rcl-bttn__loading' );
        
        var subTab = e.result.post.subtab_id;
        if(subTab !== 'incoming-friends'){
            bttn.removeClass( 'rcl-bttn__active' );
        }
    } else {
        bttn.removeClass( 'rcl-bttn__active' );
    }
}

//динамический js хук wp-recall/assets/js/core.js

//console.log(prop.data.action); // даст например: pfm_ajax_action
//rcl_do_action(prop.data.action, result);
rcl_add_action('frnd_send_offer','frnd_catch_form');
function frnd_catch_form(){
     jQuery( '#frnd_message' ).focus();
}
