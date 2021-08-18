let $ = require('jquery');
let bCpt = 0;
const showBloc = (blocIndex)=>{
    const blocs = $(".__product_img_item");
    const dot = $('.dot');
    $(blocs[blocIndex]).removeClass('__product_img_item').addClass('__image_active');
    $(dot[blocIndex]).removeClass('dot').addClass('dot_active')
}
const nextBloc = (blocIndex)=>{
    let active_bloc = $('.__image_active');
    let active_dot = $('.dot_active');
    $(active_bloc).removeClass('__image_active').addClass('__product_img_item');
    $(active_dot).removeClass('dot_active').addClass('dot')
    showBloc(blocIndex);
}
const prevBloc = (blocIndex)=>{
    let active_bloc = $('.__image_active');
    let active_dot = $('.dot_active');
    $(active_bloc).removeClass('__image_active').addClass('__product_img_item');
    $(active_dot).removeClass('dot_active').addClass('dot')
    showBloc(blocIndex);
}
$(document).ready(function (){
    showBloc(0);
    const nbBlocs = $('#images').data('nb_img')
    $("#__right").click(function (){
        bCpt += 1
        if(bCpt >= nbBlocs){
            bCpt = 0;
        }
        nextBloc(bCpt);
    })
    $("#__left").click(function (){
        bCpt -= 1
        if(bCpt < 0){
            bCpt = nbBlocs-1;
        }
        prevBloc(bCpt);
    })
})