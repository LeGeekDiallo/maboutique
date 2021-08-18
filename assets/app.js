/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/ma_boutique.css';
import './styles/bootstrap.css';
import './styles/media_query.css';
import './styles/app.scss';
import './styles/app_queries.scss';
import 'animate.css'

// start the Stimulus application
import './bootstrap';
import './js/__slides';
import './js/new_order_notif';

const $ = require('jquery');

$(document).ready(()=>{
    console.log(window.navigator.userAgent);
    window.onscroll = ()=>{
        let offset = window.pageYOffset;
        if(offset > 0){
            $('.__nav_bar').css({
                'backgroundColor': '#ced4da'
            })
        }else {
            $('.__nav_bar').css({
                'backgroundColor': 'white'
            })
        }
    }
})