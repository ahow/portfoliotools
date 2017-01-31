<!-- Event Name Guest List -->
  
  <div class="container" id="guestlist">
    <div class="row">

     <div class="col-lg-5">

       <h4 class="eventformtitle">Регистрация участников</h4>
       <br clear="all" /> 


       <h3>Организация участника</h3>

       <div class="form-group">
        <input type="text" class="form-control"  name="orgname" placeholder="Наименование организации (школы) " data-validate="req,minlen=2">
       </div>

       <div class="form-group">
         <input type="tel" class="form-control" name="phoneorg" placeholder="Телефон" data-validate="req,minlen=5,maxlen=15,regexp='^[\+]?[0-9]+$',msg='Неверный формат номера! Пример: +79111234576'">
       </div>

       <div class="form-group">
         <input type="text" class="form-control"  name="address" placeholder="Адрес " data-validate="req,minlen=10">
       </div>

       <div class="form-group">
         <input type="tel" class="form-control" name="postindex" placeholder="Почтовый индекс" data-validate="req,minlen=6,maxlen=6,regexp='^[0-9]+$',msg='Неверный почтового индекса! Пример: 670031'">
       </div>

       <h3>Участник</h3>
       <div class="form-group">
        <input type="text" class="form-control"  name="fullname" placeholder="Ф.И.О. участника" data-validate="req,minlen=2">
       </div>
       
       <div class="form-group">
           <label  class="control-label" for="classno">класс участника</label>
<select  id="classno" name="classno" class="form-control" data-validate="req">
					<option></option>
					<option>11</option>
					<option>10</option>
					<option>9</option>
					<option>8</option>
					<option>7</option>
					<option>6</option>
					<option>5</option>
					<option>4</option>
					<option>3</option>
					<option>2</option>
					<option>1</option>
				</select>
     </div>
     
      <div class="form-group">
        <input type="email" class="form-control" id="email" name="email" placeholder="Email " data-validate="req,email">
      </div>

      <div class="form-group">
        <input type="text" class="form-control"  name="chief" placeholder="Ф.И.О. руководителя" data-validate="req,minlen=2">
      </div>
      
      <div class="form-group">
         <input type="tel" class="form-control" name="phone" placeholder="Телефон" data-validate="req,minlen=5,maxlen=15,regexp='^[\+]?[0-9]+$',msg='Неверный формат номера! Пример: +79111234576'">
      </div>

       <!-- Button -->
      <div class="form-group">
        <center> <button id="bsubscribe" class="btn btn-success btn-lg btn-font">Зарегистрироваться</button> </center>
      </div>
      <!-- Button -->


    </div>  <!-- col-lg-6 -->
  </div>  <!-- row -->
</div>   <!-- container -->

<br clear="all" /> <br />
