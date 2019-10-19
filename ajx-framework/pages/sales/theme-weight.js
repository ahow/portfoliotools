$(function(){
    console.log('Started')
    
    function recalc(e) {
        let td = $(e.target);
        td.next().text(100-td.text())
        console.log( $(e.target).text() )
    }

    $('.w-comp-weight').blur(recalc)

    $('.w-bsave').click(function(){
        let trs = $('.w-weight-list tr')
        let d = { rows:[] };
        for (let i=0; i<trs.length; i++)
        { let tds = $(trs[i]).find('td')
          let id = $(trs[i]).attr('data-id');
          let v1= $(tds[1]).text(), v2 = $(tds[2]).text()
          if (v1=='' && v2=='') 
          {  $(tds[1]).text(50);
             $(tds[2]).text(50);
             v1 = 50;
             v2 = 50;
          }
          v1*=1;
          v2*=1;
          d.rows.push( {sw:v2, cw:v1, id:id} )
        }
        console.log(d)
        ajx('/pages/sales/SaveThemeWeight', d, function(r) {
            setOk(r.info)
        })
    })
})