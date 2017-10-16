function previewCSV(selector, fileInput)
{
      function parseCSV(str,delim) 
      { var arr = [];
        var quote = false; 
        for (var row = col = c = 0; c < str.length; c++) {
            var cc = str[c], nc = str[c+1];        // current character, next character
            arr[row] = arr[row] || [];             // create a new row if necessary
            arr[row][col] = arr[row][col] || '';   // create a new column (start with empty string) if necessary
            if (cc == '"' && quote && nc == '"') { arr[row][col] += cc; ++c; continue; }

            if (cc == '"') { quote = !quote; continue; }
            if (cc == delim && !quote) { ++col; continue; }

            // If it's a newline and we're not in a quoted field, move on to the next
            // row and move to column 0 of that new row
            if (cc == '\n' && !quote) { ++row; col = 0; continue; }
            // Otherwise, append the current character to the current column
            arr[row][col] += cc;
        }
        return arr;
    }
    
      function semicolonCount(s)
      { var r = '', n=0;
        for (var i=0; i<s.length; i++)
        { r+=s.charAt(i);
          if (s.charAt(i)==';') n++; else
          if (s.charAt(i)=="\n") break;
        }
        return n;
      }
    
    
      function renderTable(selector, d)
      { var rows = d.split("\n");
        var s = '<table class="table table-striped">';            
        var csv;
        
        if (semicolonCount(d)>0)
            csv = parseCSV(d,';');
        else 
            csv = parseCSV(d,',');
        
        if (csv.length>0)
        { var tds = csv[0];
          s+='<tr>';
          for  (var i=0; i<tds.length; i++)
          {  var v = tds[i].replace(/^\"+|\"+$/g, '');
              s+='<th>'+v+'</th>';
          }
          s+='</tr>';  
         
          for (var i=1; i<csv.length-1 && i<11; i++)              
          {   var tds = csv[i];
              if (tds.length>0 && (rows[i].trim()!='') )
              {   s+='<tr>';
                  for (var j=0; j<tds.length; j++)
                  {  s+='<td>'+tds[j]+'</td>';
                  }
                  s+='</tr>';  
              }
          }
         
          s+='</table>';
          $(selector).html(s);
        }
      }
      
    $(fileInput).change( function (){
        var reader = new FileReader();
        reader.onload = function (e) {
            renderTable(selector, e.target.result.substr(0,1024) );
        };        
        reader.readAsText(this.files[0]);
     });
}
