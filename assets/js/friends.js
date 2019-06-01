/* global ssi_modal */
function frnd_offer( e ) {
    var th = jQuery( e );
    th.addClass( 'frnd_disabled' );

    rcl_preloader_show( th );

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

            th.removeClass( 'frnd_disabled' );
        }
    } );

    return false;
}

function frnd_operations( e ) {
    var th = jQuery( e );
    th.addClass( 'frnd_disabled' );

    rcl_preloader_show( th );

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

            th.removeClass( 'frnd_disabled' );
        }
    } );

    return false;
}