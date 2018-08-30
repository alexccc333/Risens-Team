document.getElementById("dlbtn").href = dl;

var zoomed=false;
var x=0;
var t=0;
draw(x);
document.onkeydown = function(e) {
    e = e || window.event;
    if (e.keyCode == 37 && x>0) 
        change_page_prev();
    
    if (e.keyCode == 39 && (x+1<pages.length)) 
        change_page_next();
} 
function change_page_next(){
        t=x+1;
    if (x+1<pages.length) {
        draw(t);
    } 
}

function change_page_prev(){
        t=x-1;
    if (x>0) {
        draw(t);
    } 
}
function zoom(){
}
function draw(k){
        x=k;
        document.getElementById("pageselect").selectedIndex=x;
        $('#pageselect').selectpicker('refresh');
        if (x!=0) document.getElementById("before").src=pages[x-1];
        document.getElementById("original").src=pages[x];
        document.getElementById("current").src=pages[x];
        if (zoomed==true) document.getElementById("current").style.height=(window.screen.height*0.93)+"px";
        else document.getElementById("current").style.height=document.getElementById("original").style.height;
        if (x+1<pages.length) document.getElementById("after").src=pages[x+1];
        fitWindow();
        //document.getElementById("current").scrollIntoView();
        
}