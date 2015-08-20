function editableGridview(gridId) {
    
    var myId = gridId;
    var _editableGridview;
    
    function editableGridview() {
        _editableGridview = this;
    }
		
	
    this.drawEditor = function (el) {
        el=$(el);
        console.log('drawEditor start');
        if (el.attr('class')!='editColumn' || el.children('.inplace-editor').size() > 0 ) return false; //|

        wd=el.width();
        txt=el.text();
        colour = txt.substring(1);

        console.log('colour:' + txt);

        switch (el.data('widget')) {
            case 'colpick':
                console.log('drawEditor colpick');
                var input = el.find('span');
                input.addClass('inplace-editor inEdit');

                console.log('drawEditor input:');
                console.log(input);

                $(input).colpick({
                    layout:'hex',
                    //submit:0,
                    color: colour,
                    //colorScheme:'light',
                    onSubmit:function(hsb,hex,rgb,elm,bySetColor) {
                            $(elm).css('border-left','');
                            $(elm).css('border-left','20px solid #'+hex);
                            // Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
                            if(!bySetColor) {
                                $(elm).html(' #'+hex);
                                $(elm).val('#'+hex);
                            }
                            input = $('.inEdit');
                            input.colpickHide();
                            gv = $(this.el).parents('.gridview').data('name');
                            window[gv].updateDb(input);
                            // remove the classes regardless of the outcome of the update
                            input.removeClass('inplace-editor inEdit');

                    },
                    onShow:function(el) {
                        if (!$(this).hasClass('inEdit'))
                            $(this).addClass('inplace-editor inEdit');
                        //debugger;
                    }
                    }).keyup(function(){
                            $(this).colpickSetColor(this.value);
                    });
                break;
            default:
                console.log('drawEditor default');
                var t = $("<span class='inplace-editor'><textarea class='inEdit'/></textarea></span>");
                el.html(t);

                var input = el.find('textarea');
                input
                    .val(txt)
                    .attr('rows',parseInt(el.height()/19))
                    .width(wd)
                    .focus()
                    .select();
        }
    }
    this.createTimeoutHandler = function (el) {
            //gv = $(el).parents('.gridview').data('name');
            reloadStylesheets();
            return function() { el.css('background', ''); };
    }

    this.updateDb = function (el) {
        self = this;
        console.log('Starting Update');
        el=$(el);
        data=el.find('.inEdit').val();  // this catches input fields
        if (data == undefined) data = el.html(); // this should catch colour fields
        if (data == undefined) {        
            el.removeClass('inEdit');
            console.log('Couldn\' find any data');
            return false;
        }

        key = $(el).closest('tr').data('key');
        //check to see if the grid has filters - then we can get the column names from there
        if ( $(this.gridview).find('.filters').length ) {
            cell=col=$(thisId).find('.filters').children()[col];
            field=$(cell).children()[0].name;
        } else {
            // otherwise we have to rely on the user defining the data-name in the column
            field = el.closest('td').data('column');
        }
        var postData={};
        postData[field]=data;
        postData['_csrf']=this.csrfToken;
        postData['json']=true;
        if (typeof this.otherPostData == 'object') {
           for (var attrname in this.otherPostData) { postData[attrname] = this.otherPostData[attrname]; }
        }
        if (this.includeKey)
            url = this.updateUrl + this.key;
        else
            url = this.updateUrl;
        $.ajax({
            url: url,
            data: $.param(postData),
            type: 'POST'
            })
            .done(function(data) {
                pdata=$.parseJSON(data);
                if (pdata.status || pdata.status=='true' || pdata.status=='ok') {
                    el.css('background', self.colorSuccess);
                    //gv = $(el).parents('.gridview').data('name');
                    setTimeout(self.createTimeoutHandler(el),self.varTimeout);
                } else {
                    el.css('background', self.colorFailure);
                }
            });
        return data;
    }
    this.addRow = function (el) {
        $.ajax({
            url: this.createUrl,
            type: 'GET'
            })
            .done(function(data) {
                gv = $(el).parents('.gridview').data('name');
                window[gv].gridview.find('tbody').append(data);
                window[gv].gridview.find('.add-new').prop('disabled', true);
        });

    }
    this.addRowSave = function (trow) {
        self = this;
        
        data=$(trow).find('input').serialize();
        $.ajax({
            url: this.createUrl,
            data: data,
            type: 'POST'
            })
            .done(function(data, gv) {
                //pdata=$.parseJSON(data);
                if ( data.status=='true' || data.status=='ok' || data.status==true) {
                    gv = $(trow).parents('.gridview').data('name');
                    window[gv].refreshGrid(gv);
                } else {
                   if ( data.errors != '') 
                        alert (data.message );
                   else
                        alert('There was an error saving the data.  Please report this problem.');
                }
        });
    }
    this.refreshGrid = function (gv) {
        if (this.refreshUrl != '') {
            $.ajax({
                    url: this.refreshUrl,
                    type: 'GET'
                    })
                    .done(function(data) {
                        window[gv].gridview.html(data);
                });
                setTimeout( function(){ $('.add-new').focus() } , this.varTimeout);
        }
    }
    /**
    this.cloneRow = function() {
        dk = $(this.hisId).find('table tbody tr:last').clone().appendTo('#statusesGrid table tbody');
        dkn = $(this.hisId).find('table tbody tr').length;
        dk.attr('data-key', 'X' + dkn);
        dk.data('key', 'X' + dkn);
        dk.find('td:first').html(dk.data('key'));
        dk.find('td:eq(1)').html('description');
        dk.find('td:eq(2)').html('#FF6600');
        console.log(dk);
    }
    **/
    this.drawText = function (el) {
        el=$(el);
        if (el.children('.inplace-editor')) {
            data=el.find('.inEdit').val();
            if (data!=undefined) {
                d=this.updateDb(el);
                el.html( d );
            }
        }
    }

    this.deleteRow = function (trow) {
        id = $(trow).parents('tr').data('key');
        var postData={};
        postData['id'] = id;
        postData['_csrf'] = this.csrfToken;
        postData['json'] = true;

        //if (this.includeKey)
            url = this.deleteUrl + id;
        //else
        //    url = this.deleteUrl;
        
        $.ajax({
            url: url ,
            type: 'POST'
            })
            .done(function(data) {
                    if ( data.status=='true' || data.status=='ok' ) {
                        gv = $(trow).parents('.gridview').data('name');
                        window[gv].refreshGrid(gv);
                    } else {
                        alert('There was an error saving the data.  Please report this problem.');
                    }
        });
    }
    
    
}


