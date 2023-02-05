function redirectValue(event) {
        
    let loc = location;
    let value = event.target.value;
        
    if(loc.search !== '')
        value += loc.search;
        
    if(loc.hash !== '')
        value += loc.hash;
        
    location.href =value;

    //URLの場合はこちら
    //location.href = value;
}
    
let selectbox = document.getElementById('state');
selectbox.addEventListener('change',redirectValue, false);