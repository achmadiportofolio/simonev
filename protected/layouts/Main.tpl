<?xml version="1.0"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<com:THead>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="content-language" content="en" />
	<meta name="robots" content="noindex,nofollow" />
	<link rel="stylesheet" media="screen,projection" type="text/css" href="<%=$this->Page->theme->baseUrl%>/css/reset.css" /> <!-- RESET -->
	<link rel="stylesheet" media="screen,projection" type="text/css" href="<%=$this->Page->theme->baseUrl%>/css/main.css" />
	<link rel="stylesheet" media="screen,projection" type="text/css" href="<%=$this->Page->theme->baseUrl%>/css/2col.css" title="2col" />
	<link rel="alternate stylesheet" media="screen,projection" type="text/css" href="<%=$this->Page->theme->baseUrl%>/css/1col.css" title="1col" />
	<!--[if lte IE 6]><link rel="stylesheet" media="screen,projection" type="text/css" href="<%=$this->Page->theme->baseUrl%>/css/main-ie6.css" /><![endif]--> <!-- MSIE6 -->
	<link rel="stylesheet" media="screen,projection" type="text/css" href="<%=$this->Page->theme->baseUrl%>/css/style.css" />
	<link rel="stylesheet" media="screen,projection" type="text/css" href="<%=$this->Page->theme->baseUrl%>/css/mystyle.css" />
    <link rel="stylesheet" href="<%=$this->page->setup->getAddress()%>/resources/chosen.css" />
</com:THead>
<body>
<com:TForm>
<script src="<%=$this->page->setup->getAddress()%>/resources/jquery.js" type="text/javascript"></script>
<com:TClientScript>
    jQuery.noConflict();
</com:TClientScript>
<script src="<%=$this->page->setup->getAddress()%>/resources/system.js" type="text/javascript"></script>
<com:TPanel Visible="<%=$this->User->isGuest%>">
    <com:TContentPlaceHolder ID="logincontent" />        
</com:TPanel>
<com:TPanel Visible="<%=!$this->User->isGuest%>">
<script src="<%=$this->page->setup->getAddress()%>/resources/event.simulate.js" type="text/javascript"></script>
<script src="<%=$this->page->setup->getAddress()%>/resources/chosen.proto.js" type="text/javascript"></script>
<script type="text/javascript" src="<%=$this->Page->theme->baseUrl%>/js/switcher.js"></script>
<script type="text/javascript" src="<%=$this->Page->theme->baseUrl%>/js/toggle.js"></script>
<script type="text/javascript" src="<%=$this->Page->theme->baseUrl%>/js/ui.core.js"></script>
<script type="text/javascript" src="<%=$this->Page->theme->baseUrl%>/js/ui.tabs.js"></script>	
<div class="main">
	<!-- Tray -->
	<div id="tray" class="box">
		<p class="f-left box">
			<!-- Switcher -->
			<span class="f-left" id="switcher">
				<a href="#" rel="1col" class="styleswitch ico-col1" title="Display one column"><img src="<%=$this->Page->theme->baseUrl%>/design/switcher-1col.gif" alt="1 Column" /></a>
				<a href="#" rel="2col" class="styleswitch ico-col2" title="Display two columns"><img src="<%=$this->Page->theme->baseUrl%>/design/switcher-2col.gif" alt="2 Columns" /></a>
			</span>
			Badan Perencanaan dan Pembangunan Daerah Kabupaten Bintan : <strong>Sistem Informasi Pelaporan Pembangunan</strong>
		</p>

		<p class="f-right">User: <strong><a href="<%=$this->Service->constructUrl($this->Page->Pengguna->getTipeUser().'.Profiles')%>"><%=$this->Page->Pengguna->getUsername();%></a> [<%=$this->Page->Pengguna->getRolename().' '.$this->Page->Pengguna->getDataUser('nama_unit');%> ]</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong><a href="<%=$this->Service->constructUrl('Logout')%>" id="logout">Log out</a></strong></p>

	</div> <!--  /tray -->

	<hr class="noscreen" />

	<!-- Menu -->
    <com:TPanel Visible="<%=$this->Page->Pengguna->getPageUser()=='m'%>">
	<div id="menu" class="box">		
		<ul class="box">
			<li<%=$this->Page->showDashboard==true?' id="menu-active"':''%>><a href="<%=$this->Service->constructUrl('m.Home')%>"><span>Dashboard</span></a></li> <!-- Active -->
			<li<%=$this->Page->showDMaster==true?' id="menu-active"':''%>><a href="<%=$this->Service->constructUrl('m.DMaster')%>"><span>Data Master</span></a></li>            
			<li<%=$this->Page->showBelanja==true?' id="menu-active"':''%>><a href="<%=$this->Service->constructUrl('m.Belanja')%>"><span>Belanja</span></a></li>
			<li<%=$this->Page->showReports==true?' id="menu-active"':''%>><a href="<%=$this->Service->constructUrl('m.Reports')%>"><span>Reports</span></a></li>
			<li<%=$this->Page->showUsers==true?' id="menu-active"':''%>><a href="<%=$this->Service->constructUrl('m.Users')%>"><span>Users</span></a></li>			
            <li<%=$this->Page->showSetting==true?' id="menu-active"':''%>><a href="<%=$this->Service->constructUrl('m.Setting')%>"><span>Setting</span></a></li>			
		</ul>
	</div> <!-- /header -->
    </com:TPanel>
    <com:TPanel Visible="<%=$this->Page->Pengguna->getPageUser()=='d'%>">
	<div id="menu" class="box">
		<ul class="box f-right">
			<li><a href="#"><span><strong>Visit Site &raquo;</strong></span></a></li>
		</ul>
		<ul class="box">
			<li<%=$this->Page->showDashboard==true?' id="menu-active"':''%>><a href="<%=$this->Service->constructUrl('d.Home')%>"><span>Dashboard</span></a></li> <!-- Active -->
			<li<%=$this->Page->showDMaster==true?' id="menu-active"':''%>><a href="<%=$this->Service->constructUrl('d.DMaster')%>"><span>Data Master</span></a></li>            
			<li<%=$this->Page->showBelanja==true?' id="menu-active"':''%>><a href="<%=$this->Service->constructUrl('d.Belanja')%>"><span>Belanja</span></a></li>
			<li<%=$this->Page->showReports==true?' id="menu-active"':''%>><a href="<%=$this->Service->constructUrl('d.Reports')%>"><span>Reports</span></a></li>
			<li<%=$this->Page->showUsers==true?' id="menu-active"':''%>><a href="<%=$this->Service->constructUrl('d.Users')%>"><span>Users</span></a></li>			            
		</ul>        
	</div> <!-- /header -->
    </com:TPanel>
     <com:TPanel Visible="<%=$this->Page->Pengguna->getPageUser()=='s'%>">
	<div id="menu" class="box">
		<ul class="box f-right">
			<li><a href="#"><span><strong>Visit Site &raquo;</strong></span></a></li>
		</ul>
		<ul class="box">
			<li<%=$this->Page->showDashboard==true?' id="menu-active"':''%>><a href="<%=$this->Service->constructUrl('s.Home')%>"><span>Dashboard</span></a></li> <!-- Active -->			
			<li<%=$this->Page->showPendapatan==true?' id="menu-active"':''%>><a href="<%=$this->Service->constructUrl('s.Pendapatan')%>"><span>Pendapatan</span></a></li>
			<li<%=$this->Page->showBelanja==true?' id="menu-active"':''%>><a href="<%=$this->Service->constructUrl('s.Belanja')%>"><span>Belanja</span></a></li>
			<li<%=$this->Page->showReports==true?' id="menu-active"':''%>><a href="<%=$this->Service->constructUrl('s.Reports')%>"><span>Reports</span></a></li>			
		</ul>
	</div> <!-- /header -->
    </com:TPanel>
    <com:TPanel Visible="<%=$this->Page->Pengguna->getPageUser()=='t'%>">
	<div id="menu" class="box">
		<ul class="box f-right">
			<li><a href="#"><span><strong>Visit Site &raquo;</strong></span></a></li>
		</ul>
		<ul class="box">
			<li<%=$this->Page->showDashboard==true?' id="menu-active"':''%>><a href="<%=$this->Service->constructUrl('t.Home')%>"><span>Dashboard</span></a></li> <!-- Active -->			
            <li<%=$this->Page->showPendapatan==true?' id="menu-active"':''%>><a href="<%=$this->Service->constructUrl('t.Pendapatan')%>"><span>Pendapatan</span></a></li>
			<li<%=$this->Page->showBelanja==true?' id="menu-active"':''%>><a href="<%=$this->Service->constructUrl('t.Belanja')%>"><span>Belanja</span></a></li>
			<li<%=$this->Page->showReports==true?' id="menu-active"':''%>><a href="<%=$this->Service->constructUrl('t.Reports')%>"><span>Reports</span></a></li>			
		</ul>
	</div> <!-- /header -->
    </com:TPanel>
	<hr class="noscreen" />

	<!-- Columns -->
	<div id="cols" class="box">

		<!-- Aside (Left Column) -->
		<div id="aside" class="box">
			<div class="padding box">
				<!-- Logo (Max. width = 200px) -->
                <p id="logo"><a href="<%=$this->Service->constructUrl($this->Page->Pengguna->getTipeUser().'.Profiles')%>"><img width="150" src="<%=$this->page->setup->getAddress()%>/media/user/<%=$this->page->userid%>.jpg" onerror="no_photo(this,'<%=$this->page->setup->getAddress()%>/resources/logobintan.png')" alt="Badan Perencanaan dan Pembangunan Daerah" title="Visit Site" /></a></p>                
			</div> <!-- /padding -->
            <com:TContentPlaceHolder ID="leftcontent" />
		</div> <!-- /aside -->

		<hr class="noscreen" />

		<!-- Content (Right Column) -->
		<div id="content" class="box">
            <div id="loadingbar" style="display: none;">
                <img src="<%=$this->Page->theme->baseUrl%>/design/ajax-loader-2.gif" class="ajax-loader"/>
            </div>            
            <com:TContentPlaceHolder ID="contenttoolbar" />
            <br />
			<h1><com:TContentPlaceHolder ID="header" /></h1>            
            <br />
            <com:TContentPlaceHolder ID="content" />
		</div> <!-- /content -->
	</div> <!-- /cols -->
	<hr class="noscreen" />
	<!-- Footer -->
	<div id="footer" class="box">
        <p class="f-left">&copy; 2013 <a href="#">Badan Perencanaan dan Pembangunan Daerah Kabupaten Bintan</a>, All Rights Reserved &reg;</p>       
	</div> <!-- /footer -->
</div> <!-- /main -->
<com:TJavascriptLogger />
</com:TPanel>
</com:TForm>
</body>
</html>