const $ = require('jquery');

const askForDelete = async (url, imageComponent)=>{
    const request = {
        method: 'POST',
        headers: { "Content-Type": "application/json" }
    }
    const response = await fetch(url, request);
    if(response.ok){
        const state = await response.json();
        if(state.status === "Ok")
            imageComponent.remove();
    }
}

$(document).ready(()=>{
    $('.delete_btn').click(function () {
        let targetUrl = $(this).data('url');
        let imageComponent = $(this).parent();
        //imageComponent.remove()
        askForDelete(targetUrl, imageComponent);
    })
})