function getUrlKey(name) {
    return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.href) || [, ""])[1].replace(/\+/g, '%20')) || null
}

/* json字符串转对象 */
function json_to_obj(_data_,type = 'local') {
    if(typeof _data_ === 'object'){
        return _data_;
    }
    if(!_data_){
        return {};
    }
    if(type === 'local'){
        return eval('(' + _data_ + ')');
    }
    else{
        let json_str = _data_.replace(new RegExp('&quot;', "gm"), '"');
        return JSON.parse(json_str);
    }
}

function empty(value){
    return typeof value === 'undefined' || value === '' || value === false || value === null;
}