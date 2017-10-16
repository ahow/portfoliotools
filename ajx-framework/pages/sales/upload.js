function taskProgress(selector, scriptpath)
{   var es;
    var onsuccess = null;

    function start() 
    {   // console.log('started: '+selector+' '+scriptpath);
        es = new EventSource(scriptpath);
          
        //a message is received
        es.addEventListener('message', function(e) 
        {   // console.log(e.data);
            var res = JSON.parse( e.data );            
            // console.log(res);
              
            if (e.lastEventId == 'CLOSE') 
            {   $(selector+' .progress-bar').removeClass('active');
                // console.log('Received CLOSE closing');
                es.close();
                if (onsuccess!=null) onsuccess();
                if (res.errors>0)
                { setError('Errors: '+res.errors+' <a href="/html.php/pages/sales/errors/'+res.errfile+'">Download error log</a>');
                }
            } 
            else if (e.lastEventId == 'ERROR')
            {   setError(res.errmsg);
            }
            else if (e.lastEventId == 'LINE_ERR')
            {  console.log(res);
            }
            else 
            {
                if (res.proc!=undefined)
                {   var proc = res.proc+'%';
                    $(selector+' .progress-bar').html(proc).css('width',proc);
                }
            }
        });
          
        es.addEventListener('error', function(e){
			console.log(e);           
            setError('Import Error occurred!');
            es.close();
        });
    }
      
    function success(fu){ onsuccess=fu; }
    
    function stop() 
    {
        es.close();
        console.log('Interrupted');
    }
    
    return {stop:stop, start:start, success:success}
}



$(function(){

    new previewCSV('#preview_division','#division_details');
    new previewCSV('#preview_sic_desc','#sic_desc');
    new previewCSV('#preview_company_list','#company_list');
    new previewCSV('#preview_isin','#isin_matching');
    
    var ctrls = $(".progress");
    for (var i=0; i<ctrls.length; i++)
    {   var pb = $(ctrls[i]);
        var id = $(ctrls[i]).attr('id');
        var path = $(ctrls[i]).attr('data-path');
        task = new taskProgress('#'+id, path);
        task.start();
        task.success(function(){
            pb.after('<div class="alert alert-success"><b>Data was imported!</b></div>')
        });
    }

});

