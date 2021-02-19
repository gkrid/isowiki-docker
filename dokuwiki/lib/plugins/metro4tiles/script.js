var metro4tiles = {};

metro4tiles.resizeIframe = function(iframe) {

};

jQuery(function() {
    var $textarea = jQuery('#metro4tiles__editor');
    if ($textarea.length > 0) {
        var $ace = jQuery('<div id="metro4tiles__ace_editor" style="height: 500px; width: 600px"></div>');
        $ace.text($textarea.val());
        $textarea.after($ace).hide();
        //https://stackoverflow.com/questions/33232632/how-can-i-remove-the-first-doctype-tooltip-of-the-ace-editor-in-my-html-editor
        var editor = ace.edit("metro4tiles__ace_editor", {
            mode: "ace/mode/html"
        });
        //https://coderwall.com/p/4lrv6a/ace-editor-form-submit
        editor.getSession().on("change", function () {
            $textarea.val(editor.getSession().getValue());
        });
    }

    jQuery(".metro4tiles__iframe").load(function () {
        this.height = this.contentWindow.document.documentElement.scrollHeight + "px";
    });
});
