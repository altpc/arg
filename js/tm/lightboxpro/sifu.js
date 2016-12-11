var SimpleIframeFileUploder = function() {
    
    function _getIframeName() {
        return 'sifu' + Math.floor(Math.random() * 99999);
    }
    
    var _config = {
//        form:
//        action:
        iframeName: _getIframeName()
    };

    function _buildIframe() {
        var iframeName = _config.iframeName;
        var div = document.createElement('div');
        div.innerHTML = '<iframe style="display:none" src="about:blank"' 
                      + ' id="' + iframeName + '"' 
                      + ' name="' + iframeName + '"'
                      + '></iframe>';
        document.body.appendChild(div);
        var iframe = document.getElementById(iframeName);
        
        iframe.onload = function() {
            
            var form = _config.form;
            form.setAttribute('action', _config.oldAction);
//            //onComplete
            if ('function' == typeof(_config.onComplete)) {
                _config.onComplete();
            }
            var iframe = document.getElementById(_config.iframeName);
            var content;
            if (iframe.contentDocument) {
                content = iframe.contentDocument;
            } else if (iframe.contentWindow) {
                content = iframe.contentWindow.document;
            } else {
                content = window.frames[id].document;
            }
            if (content.location.href == "about:blank") {
                //onFailure
                return false;
            }
            
            var response = content.body.innerHTML;
            response = response.evalJSON();
            if ('function' == typeof(_config.onSuccess)) {
                return _config.onSuccess(response);
            }
            return true; 
        }
    }
    
    function _getPrepareForm() {
        var form = _config.form;
        _config.oldAction = form.getAttribute('action');
        
        form.setAttribute('action', _config.action);
        form.setAttribute('enctype', 'multipart/form-data');
        form.setAttribute('target', _config.iframeName);
        return form;
    }
    
    return {
//        setForm: function(form){
//            Object.extend(_config, {form: form});
//        },
        upload: function(config) {
            Object.extend(_config, config);
            
            if ('function' == typeof(_config.onUploading)) {
                _config.onUploading();
            } 
            _buildIframe();
            var form = _getPrepareForm();
            form.submit();
            return this;
        }
    }
}();