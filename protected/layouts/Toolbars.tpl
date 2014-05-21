<table width="100%" class="list">
    <tr>
        <td width="120" class="left">Tahun Anggaran</td>
        <td class="left"><com:TActiveDropDownList ID="toolbarOptionsTahunAnggaran" OnSelectedIndexChanged="Page.changeTahunAnggaran">                    
                <prop:ClientSide.OnPreDispatch>
                    $('loadingbar').show();
                </prop:ClientSide.OnPreDispatch>
                <prop:ClientSide.onComplete>
                    $('loadingbar').hide();
                </prop:ClientSide.OnComplete>	
            </com:TActiveDropDownList>
        </td>
    </tr>    
    <tr>
        <td class="left">Bulan Realisasi</td>
        <td class="left"><com:TActiveDropDownList ID="toolbarOptionsBulanRealisasi" OnSelectedIndexChanged="Page.changeBulanRealisasi">                    
                <prop:ClientSide.OnPreDispatch>
                    $('loadingbar').show();
                </prop:ClientSide.OnPreDispatch>
                <prop:ClientSide.onComplete>
                    $('loadingbar').hide();
                </prop:ClientSide.OnComplete>	
            </com:TActiveDropDownList>
        </td>
    </tr> 
</table>
