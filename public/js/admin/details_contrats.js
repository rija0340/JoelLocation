
$(document).ready(function () {

    // var text = 'test';
    // text.style.fontWeight = 'bold';

    //******************get elements************* */
    var agenceDepartElem;
    var agenceRetourElem;
    var dateDepartElem;
    var dateRetourElem;
    var vehiculeElem;
    var nomclientElem;
    var emailClientElem;
    var prenomClientElem;
    var numeroClientElem;
    var lieuSejourElem;
    var dureeElem;
    var tarifElem;
    var vehCarbElem;
    var vehIMElem;
    var btnAjoutConducteur;

    //*****************values elem**********************
    var agenceDepartValue;
    var agenceRetourValue;
    var dateDepartValue;
    var dateRetourValue;
    var vehiculeValue;
    var nomclientValue;
    var emailClientValue;
    var prenomClientValue;
    var numeroClientValue;
    var lieuSejourValue;
    var dureeValue;
    var tarifValue;
    var idVehiculeValue;
    var vehCarbValue;
    var vehIMValue;

    //********************boutons*******************
    var btnGenererContratPDF;


    getElements();
    getValues();
    addEventListener();


    //**************************function getElements**************** */
    function getElements() {

        agenceRetourElem = document.querySelector(' .js-agenceRetour');
        agenceDepartElem = document.querySelector(' .js-agenceDepart');
        dateDepartElem = document.querySelector(' .js-dateDepart');
        dateRetourElem = document.querySelector(' .js-dateRetour');
        vehiculeElem = document.querySelector(' .js-vehicule');
        nomclientElem = document.querySelector(' .js-nom_client');
        prenomClientElem = document.querySelector(' .js-prenom_client');
        emailClientElem = document.querySelector(' .js-email_client');
        numeroClientElem = document.querySelector('.js-tel_client');
        dureeElem = document.querySelector(' .js-duree');
        tarifElem = document.querySelector('.js-prix ');
        alertVehiculeElem = document.getElementById('js-alertVehicule');
        idVehiculeElem = document.querySelector('.js-idVehicule');
        vehCarbElem = document.querySelector('.js-vehCarb');
        vehIMElem = document.querySelector('.js-vehIM');

        //btn
        btnGenererContratPDF = document.getElementById('genererContratPDF');
        btnAjoutConducteur = document.getElementById('ajouterConducteur');
    }

    //*************function getValues*********************** */
    function getValues() {
        agenceDepartValue = agenceDepartElem.innerText
        agenceRetourValue = agenceRetourElem.innerText
        dateDepartValue = dateDepartElem.innerText
        dateRetourValue = dateRetourElem.innerText;
        vehiculeValue = vehiculeElem.innerText;
        nomclientValue = nomclientElem.innerText;
        emailClientValue = emailClientElem.innerText;
        prenomClientValue = prenomClientElem.innerText;
        numeroClientValue = numeroClientElem.innerText;
        dureeValue = dureeElem.innerText;
        tarifValue = tarifElem.innerText;
        idVehiculeValue = idVehiculeElem.innerText;
        vehIMValue = vehIMElem.innerText;
        vehCarbValue = vehCarbElem.innerText;
    }

    function addEventListener() {
        btnGenererContratPDF.addEventListener('click', genererContratPDF, false);
        btnAjoutConducteur.addEventListener('click', ajouterConducteur, false);
    }



    var imgData = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAPkAAACwCAIAAABCel9jAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAE1dSURBVHhe7d3Zk13VeTbw76/JrSuXqVTlIhepXOQmQ6UyFSGxHRPAJEFYQiA0zwNIIJDQgJhHMwkwGNvYhMFYMiAzi0lgYUASMxisePp+Zz+7tzenzzm9z9Ctbuk8F7vWXnvttd71vs87rNOnu//f77vjd7/7Xdk6QSBAUN73g/LNAmXXrEclasSG3DbHAK80xGDyDIZpWqsX1084smco74dD83myKJT3A6GcYhpsVs47DTNXKBeYU8KX83afeVZzfbTooYUxTgWcQlw/+ZAwBuX9GD0x5vocRsn0AmXXGN0x5voYpwpOBq6Po9oYTTDm+hinCkbJ9THngrmrh5PbgicP160+S0w1S8QYAL0lLxQ8h51hRmuYKAvK+5GinHoCZe/QKKfrhHLEQCinGAjlFF1QDmqM8rUGqMan0Rx5vQfKcUOgnGgSyscFToZ6fYwxmmDM9TFOFXTluphftk4ETuzqDTEnhByjwpjro8GY97Mf4xqmKbC5IrTGG2+88cgjjzz44IN33323xm9/+9v6gOnDDCzRhpnZ1wxgzPVGqOx9/PjxJ5988rbbbrviiit27dq1d+/e7du3X3755b/5zW8yrBg+jZiBJU5WnCRcnxkGvP3225g9f/786667bv/+/W4/+eSTa6655vnnny9HnLxo+focd7OTJ66LuB9++OG77757+PDh119//bUCr34ZejxSfsCbb7751ltv4es777zjrWPHjr333nsffPDBxx9//Nlnn31e4IsvvjAtMPPLL78skB86dOijjz769a9/nUUfeOCBe++9VwGTYb+qwbtgkl8WMOenn37q+n//939tI42xqKdWr2AvFnLNIzBJ5vSKSciQZNIQxlcou/rBmOvDYmD1VS9iwFNPPfW9730P7X7wgx889thjP/nJTwRdlYZ+0N63b5/OJ5544scTMCx49NFH//d///fhhx/+4Q9/+NBDD32/QBrfLaBhwM0337xhw4b333/fisjHPbBQ/+rVq72rXv9RAW2dQdoeWSIgwD333LNp06adO3fu2bPnqquu0lALbd68OY3HH3/cmFKyL8PrmdMqRM1aaafhapjzA1G/U8BazhJ33HFHljOy2Nz3Ne68885rr72W0nLrxaeffpobS1MNLWIYDy9vRo1pcqq5F9cpIrqga6y96aabUFlITlw8OoEjR44I1eJf3moO04qXIP6JuAK/DICI1hL+d+zYsXbtWgX6mjVrcFSn1cNFqJidHtcAiY3EY4xEMrOBckii2L179/XXX3/fffctXLjQVMA/Dxw4gHyu8LMJ5Bbqt2kHzz77rCUuu+yy5cuXr1+/nlNt2bKFqIjO4Tkq/0waUYNZiHIojeps8LnnnuMVZLjooou2bt1KyG9/+9v3338/BxMmBA4QAriQBj1EV3VSxi5Q3g+Ecopp4Lo55yTXXZUfN9xwA24hJYprsATDCGx33XUXB2AtNhaMtVl0sCCEFiZZtWrVunXrmNkqqCkeK5P4wM9//vNXXnnl4MGDypsKuXXtBuETtwIzqKnQkTPgJcbjGa9Qbr300ksvTsB5ABefeeYZ5OYDgHDIisE//elPXdHRW5zKtboF05JZP83QD09zuuADaG0qt143s+WEBkFdvuIzHIYab7nlFi5x9dVX2zLcfvvtZiOqc7myTQrlNqWa5gjmBtcLV/+Dr+PHxRdfvHLlSuaRvlmFtTBMUpa15WXWYjNWZO+9e/cK8PXX62ibmVcgmXcxSXJXF+Gf+cVgboOIN95444oVK1DfEnyMp5VvDgFTASZhJI9yligfjAg2GDmpSNiWmrBc2LYQ30N6vpGYnVLQALkl74oRajaewN9UdOILIfVncMbMFcwlroM2oiMcXYuvsr+gpZO1BB5HT8e+4o0vwYtTxnXTYoD6ZNGiRTgNTG4J+SEQ2zydP3/+Oeecc9ppp51xxhnKmCuvvFKMZH5+JU6rB/qKdtmRM6gdqUA4KqrhfZ5Oibxeoe22DSgrQdmX+dX0vLp8UAPeUyPdSjg8gVY5vBhPFfGNlG1GCh+ivryXF2cGvTc4JaaL68QKyvvhUE2ldFFUiCgSsWLXrU5UY7yqNK8GV0hPHeWDgmeSvsgtsDnMmUeCliscecsRNYiO+iV65QRSqrmXLl3KN8AhlTy33nor6mcGlYY4+otf/EKhjGde5Ifd8oDxKIVkxFCDlb2jgwqbkFKT4odfkad8UADvFTMOrDQprclp2pTMgb0oeUqbsqg9bty40d7zFv/nEhptKh05Mj+U94NizsR1188++0zxjQoCqmSKOuIKfmBYomlDdYjxTIiLkjJeygziMdIjupxuieYnWmWMelrVJMgJmU6EoL5yQNy5c6fSVphURHEMa6lxq5Or4IpSwNMUDFIK780nMxhJNmEeXnjhBfMXdX4LnMcJIcA/KD5ZLSE1Vcitq+OBdc8++2yTWw5Zc7h05MBUk4vcZMNyAdtG6JM8k6M+5Utiy5YtU7Lzdj2c017ydE5gLp1N2UxQX7JkiWOc8pFhMInl8hSDe3Od/dg+n7ixKJI5X1a1jUObyYVV07odIIrwPXWtWGgS0R23FixYkOKeA7gqAC699FJszscv4qt8wh/kE1sjGMZjD84pHvCSP2iABuSsyU/qMB5NvSgzgEkCMqQHeJoTiJTiev/99xtsjxX4jyWUT+aRhcrNFBqoVJorAXiFXRBbwzy2WYwdRF0zjznDdeWKeLm8AKYyj9hWPusEzBN0lZhyLiuyt1MmSrnVXw6aABufe+656K4w5Q96GG9I+3EkdJcxSC4880lioJowj+XWwnjhH3VcL7nkEv4gonNj/iDG8w01gwymYeP6DZM68nkliK+5gn5PwTBTeV38hviYaTUU6xqe6jeh68KFC6U1AtCMtFbK3R3CuaSaJED/UhkkWMx5rp/wDRR8K2XYvn37V7/6VYykZadABAK8lNnFeElfIBT2hDGxCqVAxSn2IJlhOFevlTNz4NbBy6Ezny1mAFRP+0XewmmcSE8P4EqKMWkH4ch59OhRrOJ+yoxUGjwWVCM2om6paphcA49SvdQ/08wMEHWZMz8nln+M5xhqccGe7yWEV/utGnWQUyKVYTiqSXgyo+Rg03H8bMNMxPVChwOSJi8qKwU5wUkcoms5GqeTrPFJHsdy/RiP+szJhAyTSaYEw6srTHX66adbAquqanUAsfMKv1Ldolc6ZyF4lDoq3xdQR1Fg+jui0oOMKnY4LHEbBxtZgvbqA2YzTgzX0wPlfRdkgKtTI3s4DKV/eBSLl6srbxS+POSss85iP8Xohx9+mEcNkdmgvC/85+qrr274+WP9xTq69XdDX+OVVSqQtGUDG0+7tY1J81SdQoy8KmWp8h0zFEVyS8bMJCIPlPfNMHP1eindl1E+m4Q8ylUSv7b4IWj95FRHMVOJsqsxhH+Tq+Dl4m984xvqY6e0+jyZto7yQSdUA5xQ1UXpKZ50RsZXKHtr6NjZEZkByvtJk0P5oADKRkJQQQnVHUv28s0CbiVPiRTjnezVhytXrlQCZVgx/A+Y3NMNHUe21ptA2VVD+WCqJcpBBdyeGK6XXd1RHyPoOj/1+NS5Gtxk5jYIaQJ52uedd96f//mft2WPlrhfRvmgCzIgH1lUt91QzPcHlL01dOzsiMwA5f2kyaF8UABl65+iSJtUkds68mLg1sne+R7RvX7gwIHFixfzmQwrhv8Bk3u6oePI1noTKLv6R/l+Abf9cT3v9EDmhfK+hvJBgbKrhvLBxKOqofAVfhTrzlLprx41R96C8r5AbtkM0iN1fOUrX3G0zW0bMgOU9z2n5TCpgNueTkYxQYmyq4aOnR2RGaC8r6F8UKDsKj7AVbyVN7//vYNQ9UOiOsrXCrh1cLrvvvuef/55xxvtCy+88JFHHsmwYvgfMLmnGzqObK03gbKrf5TvF3A7c3F9MEisl156qXNePkWJ0MMj87hKFw5bUsfhw4edtP7oj/5I0MqYwZCZ1bIOytXtLEQ+INeIhA899FAT53z11VcV6zKAQyrGL1q0qFtomIWY7Vw/dOjQsmXLaFabGXpbojkyz2effXb77bfLwnKxSuaqq6760z/90xSg1ZjBgBAHDx4sb2YlqFShVd4UhbhQrdF711iu8nFw4szPPvvskiVLYpo5gRFwfVT86wjRUaL86U9/qj3ChTKVUy+uv/XWWxs3brzppptuvfXWBQsWOFZmzDBQd1VFV3pmG3hjPYM99dRT1UGlh8w0hutHjhzxuri+fPlyR/ny2azHbOf6o48+in+ie25HuxZHEp+OHz++du1aQV1oVywxYfl4UCi3uM0bxVdzZy3X77zzzjrXHTSrCN1DZnEB148dO4brL7zwwsqVK6sD7uxHV64PaSSvB+X9oFAO4nq3Txsno1y1y7rlswJuVaj5EvbFF198+eWXC+07duxg9WLs4Pj1r3/NbYRA7Sx0wlFtuQKO1k8UKjduXzzpBVyXCd9//33hXJG2atUqt+WzSciiUN5PD5oskQHTxfVRwRGqL643RHb3SPF7ohqC+po1a/L1r3yINgzqXJ+1wNG6V+O6UF3edAeuK89w3Zn+5Zdf7s312YMpuN4Q0+0SDz30UMV1a41qucyD1vfdd5/Gvffee/755+/evZshw/5hFkoN8+abb2qPSuCRQw1T/fIR4DollDfdUcV1jvHSSy/NFa4HI6jXpxXKjPnz5wsh2i2mT1BnSA7ldcfQlJtIP2/evA0bNjge3H///dWAwfDb3/4W16sfzfSeaoCFvBKU9wMB1/ft26eRecT4bLzq6Yic5t977z0FT+p185TPJjCkYIOhpY4JlF2TMGKul6uNbrfouGjRoseKX7SrY8gl8vo777yjRkdNDav84z/+Y35NYXj5udCrr76adsfZRqiiwSAw13/k+eSTTzb5pPzQoUO2duTIEVxnmmXLlk3OBidka00Wne1x/fDhwxJlddgfFW/yyhdffHHDDTccPXpU+8orr/zjP/7jK664ong+LFDhxRdf1Dghhm8CUbz+wyMJrSreesjMgQVylUy4vmTJkiobzH7MKNejRyjvG0CU3bFjx5YtW/JrvB3f7WvCoJCi9RabPfvssxpPPPHEn/zJnyhjiueDI9M6BuRnAlMi4wtx+t7FMPje977nLFTeFLc0oNFbkoMHD0oIGP/AAw+oYSTD+iQjRG8xBsNsj+s2LEuuXr06HxqMcP+Zav/+/flaCF/6i7/4izPOOKN4ODgyLQbkazZu0zPbQMJ6+XH33XfjbnnTHcYI5M8884wkkO8IJDnMCcxqroclSkmlRQrrkfNG6amMyS9Tb968+a/+6q8G+3OHFSKhY1+Y1GL6rOT6I488Un2nl2JvueUW5WJue+Dpp5/+4Q9/KAN4XeJavHhxfiA1O/fYhjnA9WPHjl177bXXXXddk8ADeau59m+88cZ8ZiKPf+UrX6l+BtR8hjry1ssvv3zrrbemZ3ZCBKkk/Pjjjzl8xz8TUiH7+vGPf6yy/8EPfuAQLzOsW7cuX6kYTFczjBFzvUWQ0W27muquu+66+eabXQVgneLQb4q/t+iqDRk2GBgvX47nS//wD/9QfUm1Wr0v5K0PP/xw165d1e/yTRMiZFB2NcYrr7xyzTXX5MWf/exnt912W/o7oloCv4XzO+64I18C27hx4yz/KkQdsz2uR4kqjaVLl1544YXOqerg73//+w8++KA6+7sF3LIBKCKVj4zBeM5P3vriiy8yVQ989tlnW7duxUsJZNWqVZwqi+baL6q3cP2E/H5aQ9g1rqd4I+pLL72U/t5wlKdeafbdd9+VD7ds2fLee++Vz2Y9ZvvZtILD0ze/+c158+ZJo0j5ySefsNOvfvUrHNX+6KOPdL711lviDbPJsKI1f1A0M48S00HKgHwJvo7kBN6igNHeXvyTDE6iczCuV+CTHT+PM62MpGAQ+y2kSn6t+GsIxFYPkNwGH374YXUCkWyBM3/nO9+xERqQf0RTdTbcPgE9cOeddxpgs0bed999XkksMEN250oPpJK4KMftkiVLrG6qht/LFTgs9NhjjwkHXty9ezcnSTQZUlczgznDdWym2fXr15955pl79uzBBjbLz+F7AKtQHI3CPPDis88+q7McUQDtzPn555+z5bZt21BBJ/sNbMLjx49jsBRvXSuiYL4fLy/xJeHQadt106ZNDsQaRl566aU8jRiGXXnllUYikwiqknYuR6/qKqDqzDVwmDEShOqrr77aDCEimMetAfaVvyeTvx5Dk6tXr+Yeqhdewc16/OWF6OHQoUMciVNJobzIjixdHzDLMTe4HlU6TjGnesORyOEP0QUwgS2/FNykape41ZcCp0gmRj7xxBPvF/8+AHgOUh44cABXHNq6ldot+hco7ydgZimFkwiTO3fuvPjiiwm5cuVKDQwO2zAVPxKJkQxvgPsJuiLu3gL6kSk9FVKhifSuZVfRmYbKrUI6W6Mn/m8CcACEltP2799vd8o8I/MuPdAG5xetre5R/U89ZpuuVfajNJuyhaefftpmSVsMHHN9dGiRq9C4AEnd4hMO5UvtSnPcqr4cVgxvBKGdaRkY2xj7gw8+QMFnnnnGFTPyU88eSMYw3usIdMkllyxbtmzFihUbNmwgnqCLByiLVUI7WrjiE2L9pPjT/cS2qNOwnCMD8DQvmgcvbc12lDdHjx5VDRNM2kFBpRoP5FTqHykI8v9kxOM6lHag4VHACR11FixYQADBIl/5ij5NaAtCBkdSiBMD+/mJUNLm7SaRCubPn1+dYm0wU0Ffmj9RmDM1TLTJcpI4e8jOKJXvyfz85z93+/bbbxcDO8C7PYyBWOZhb0EXz3BdXBcCURnDcAvwDO0UqZiBFgZcVvz7ioULFy4q/kivCkGn4hiVnQ3Q1+FB0vAimXusHgjw+dKlWKs8KHtHCiw/77zzOBs5J39QSHsc0tIY71hvWKIAqXgjneSP5yiN8rssXF2mysFmrmDOcB1iG/wTWmhfYZAwj4WIJfWjrBLFAPRijKqq0W6RvSfhvCLKKpeXLl0quCqjV61atWbNGqWtAldDaDzjjDOY/Gtf+9rf/M3fuBWGEcKiTpYcQygt52qMiMTZ8E+ktx2lc/UrFL0FHgDKa5kHm6Wd+lGHfkCieOWVV7gEh88XS999911egfeckJNzYK/zRo/EF7N5q5hgbmAucR1ifsFSgBFW6VqsVT6KtUptBbeyQRuYxK3Y4+SHiN2ok34o74svCwhX77zzDnMKaWJbzoscQNhTk6i/rTLCkGY2bkZmQRTdU4/1C1vg2wVpW+B4NpIPqZRAyiECq5pkIbWWEC5IU5HKShVOAFC6yGa2jNxI73UHIW+pXjBeeZPMoyG4IHp8cg5hjnEdEq2dtFAZy3HdLX6wUMyGnaKjq0gvgKFRG5ubA1dUR2pxBzITYoOor3phddRkdTFy38T/LVKFV1DIlq0CeeqqX/w2PtDpdcUY2nFdAl9++eVWMdK5GZwo5I0KbtHUuhXyOY9XoHUOLf6AOrVIfUoy6cKcCNo6t/7oR941TJ3tlCxmC+2qLEW/+p4+HQMEb9UUN1CSSWgCvEixY8cOarRfk8t1njp/z/JvcXbE3IvrQW4dT1GQdZEjlhOKHOZynhPSMqwO7yb+MbC6RfRSjuO0yscrZhDJFK+KInFdRSTAC2xbt25Vi3sdsYR5iV4BgweWRkEEQlZAiIJ1rdvQy3h0RErDcJcT5sO+tWvXOoymQEIsZLrgggt4ggjKGeSoXEH41A/2yD34Bk8zlQnrvNfm7Zkf1GB8kucAX8Vve6EiQcGO0FdgFryR24QkJLmy0DalLyrlVNZFaHv3Ij0r4lXqegQX2osmC43OGTTiesGuFsr7IVBONNVUbQPySlB21YDZsjNWYR6jCmMawPCuboPiI75787meUiG32iIfpNN4hYoX8yMYMC3DYwNmt2Vt5nc2NVJ4xj9US9w1OPBufKByAKsjWeSxVs4b119/fT6RlJSsWBQULWijIBSCtNpmMKclLGTFpBSrcwxOgoiuVJGfTEloySEk8TrxIMkB+KFHuG4w+vLzMHiWoDT2SN1plsb1gTcpZgvY0nHqVEiMF+9BpxAukEvcKlrWNb6hTr0l+oY0jm4ppdAdfTNgjFmOuVevB3V2NmHqkMgSfEMYXrBggeOpUlhcVPDkkJcxTSRpMmaMftFEq7Oa6wV5Wijva6h3dhwwclSr4LcCQMmh6t25c6eCW5mbb9rMjCRjTEYTzZ+0cV1nUN4PjXK6iQkdapW5amJFvNNb1T/GrMXJwPUZRr9LnwpuMCf2eHJyfVpVb/JpnX8uYk4oZAquj406PMY6nCUYc33aMdbhLMG01zAsHZT304n6KtO3YsOZhxegxwzTt7smOLGrD4y5Wq9PiRNuj5OY63MUHbhOj0F53wD9ju+NfqfquHq/k0DHeQZGx6myBJT3PdFjWMMZhsEMLFGhpZHpX+6kjesnBDNjs27osfSJFWyWYLZwnSV+O/FXXwLtfFmlHDEXUDBqNnJ9DJgurkfvx48ff+WVV5566qn9+/fna6jVFwDz1T8ovvpa/qpvvtNXfcUvnRlgpB5X7+ZbrPmW7JPFl8VfffXV/FpQQbYWCinGaH3R/7PPPssvELLF888/f+DAgXw//vHHH3fNN4QDbZYywLAXCrz44otp6Kl3vvTSS2Z7/fXXDx06lG8Lm//dd989evRofvMwX4vPN+SGxKgMOo1x/dlnn927dy8N5nf+6eKtt956++23jxw5cuzYMRrJ9xDz9cM6Pp2A9scFjPnggw+8wmZAoe+8847ZzGlm8//4xz++9957mcG6o1LNXISN0xLN33///ddff/22bdsuueSS1atXL1++/IILLli4cOFFF120YsWK9evXb9q0yaP8xY58033nzp27C1x33XUMd8cdd+RPHrhWuPPOOz2i6uprycF3v/vdRKKEKhHqwQcfvPvuu/W71Z8Al28j79u3j6ugAVMyK5cope+CURl0QK53W7vqF3fpMd8HzO/I3HjjjVdfffWOHTvyh0pcA22Kpm5K9woDXHzxxTEDXHrppVu3bq3Ga4BpGeaaa64xZ+Z39dR4wb4uxkmJluW/vEGx4ODBg9hGJ9RIY9HPnj17qGvjxo0bNmygHFr1iKrz75F37dpFk5Svk/b4wNKlS+fNm3f++eefd95569atM1UFr7t60evm9Aoz5ddNdHrkVqcJgTlEIoIh+l133cXx8J7bcJVvf/vb1uVsa9as0SAz1zKn5JO9wDSZb1q4Lgki99q1a21GDLBV7q4O4dz59RmerfAw7LnnnpMZeTmwlrQI4nQaesAjATu/sOOt/FZEfiXCnGYWV+655x4qFqto2bAIc9IDxSkEhzAb4Whb0KXtfAeTNlBKA8Moilpee+01eVU09aK0+fnnn+dL/PmSpnkUhGZQ4XCSX/7yl6ypCKlgmBjsFfVJfkNAVDZbfjfX5GobM7ALp1pQ/KdYhuYMMSJRBXXkJgyjq3wMFv4WL17MZ/SbKvuaG1wHO+epoXjZNVMQMyTWm266qdLayYE2bWMqroiXSXrcm7c/8MADaKoCwTNBmvM/88wzRpbvTAXFpPDk2GNaZafZygcDgf+Q4ZxzzlHQOwDwovLB73/PW5T4IhSG4Don4Q/55SxXdY76NiPtOsjt8OjK9cHWsBOpk2cLLW2KHq3cHaFUVdUIG1bPclA+m5uoy5/ffUZi5YeCATXzK6fKg6uuukqNIa6rjDFJ3C3faQwz47ec6Qoqfp2DaS9vCTf/+q//qihiFDFb4V49ChQtwjxHFf6NsRe1qGGiFU+QdjJshBYcAdfrI5XpwrlziTKj7BoCfcmQwdZVI9JaBGg+w6zFr371K+neaQ+VxWzOrK3MUBI4LCK9KkVcF5JTigSVQhqC1dA9BYz4akWdg2mvWlraOeusswRsXir6mD8D6lAIITd3RXf1laXzUVsKqsyT6/BoWsP0Xk8x56p6syWxwZXee7ziUYWy68vo1g95C8r7L0M1afWnn36aiulRT7eR0GOemUeEgfJ+4g+BILRCxXFQtENBhxnhUOZU6eK9OnjKzzGmBGPJEiaXk1UdSkElTflsCMg5y5cv56KitSiukT9907ZNePPNN5Gb1RQ/nA311VFhfLdXBkB/XKcUKVLw5n+SKUsIJwJPxog3woOSkehuhxFumHeJdMMNN+STr/TI6TqpT41Ij9IlCRMFh9fgyCFkKMfJ74CIfHKUCKcgfPfdd2kexVOdO3e6db6vm6BfZPtHjhzBdaxSCzluqSsGnrAOu3B2QhUTciRQfX1c+9uomM0Hjh49ysEMw+wYRf3jPC3ASwUa9pj+IdHH2RRjiL569Wp5k1JIxvOuvfbaVatWeUpiR1KS8eaMbw4aD8r7oYEoJCGtzCjnrFy5EvvV8SIWycm/YsUK7fz4aZbg888/Z1rBguSSksqE+dXNyPHaa6/Z0YYNG84++2zaFuMFFD7Ae9F98eLF1Q8WMlW/QCZlJ59RTigkhIPyQSfkAxk05Q+YyglRkx9yFQfN/CWZfIyGITwzn05edtll3lV32Vo+hgebAtW8TMUTMn+1Cx5OEiLlrw7y8IMHD+bRYGjE9YKHv8P1b33rWwJA2VvA3v7nf/5HJzKJl/KUIKE+owUlROCcQR1GSlLG8BDbkxYYFeGUHGAG0NCjXwA2wPYka+pAU6+4VVBiQEARholw4jcYo3yiaPwmgxBiLXPixPnnn3/4y//4Kh91DZ/9GwIz6IRgCIG4TmNUsX//frYXGpQi+Yj6wgsv3Llzp41ffvnlJMQJt8oA9qZeurVBmkQyEP7tbtOmTfkkuyEwlTBMyUC8yCTKFfyzKM1TKdJv3779Zz/72b59++RtoEbqZQWlCG0zARLTvwZ4xbsk9DQMZikm9iJpeaNtmkqIcSWAFaUOHpJPPCPVZITxyCPksybVScWCl4UGOHkH/dUwqkOGEX6OHTtGaBLrp6mFCxcyGzVR/QUXXPC3f/u3p59+OiPJs+IQLFq0SEw67bTT9J955pnz5s1bsGAB0+bvOIu7GZYfZ+ifP3++kX/913/993//9//8z/98zjnnnHfeeV4xuaeAvm7N81//9V8m/NrXvmb83/3d3/3Zn/3ZX/7lX1500UWbN2+md7GBbDjBxqQls/yDZ6ij8K321QMGTDmmDYjInHSl9IxjF/Gr9eNDzKYo0VokRgWMyV8Rw4x88pCQqUHDxEYmU7lFRGO4itv8KJ5Xi/0hundRNn+2DjnEPzYSrdFLyBT7kZU2CADkcRUayOYROkombuUNJCYepXk9P/fgmebMukSiw3wwb48iBa1OVk4qEwQlT9k1kfar4GKqCBMQiauk9A266ZwSBK96IdQcfdQwWV5coSAhlofRkfDDZbk+9uCQMeQQt8RmcYgGbZJ3eosxPHLlo27tli5oUNKENOg0agUvoiYYr013rt4Cr+sEs9E+vbuSQaYjFQuxBzOgmkigX8bHJ49S6ZLfDPZiTLGzvtFmCQLYC3JYS5DDYPbDGHncRuwaOZg5wViyxieqI4yGfZWz1GCwSL9x48bIzE9okko5hmJg3bp1gsKSJUtEEIbnD1Y0LAGVO6G41XlUnawWsjpJKJCq1Ru85cUXX+QYqYa5U18pog1ih7RMSBoQ9SRkwjtYV8Gb8BbVEJhxQ+pgQcLErMRgPj0ZTMNElY2lBfaiSfLTpJHmSZY2pjJE1Qjqjyr0wXVoex9XyIdGdkiIsncCJLNhu6XTsmt6QBFWYeOExjpQjQk5JDOUXRPoqI7mYEIcYgkshARs9maGwlXfwWnBG+c4gAH4igHo6FZ/ZdSgTRhmJrAxYiQ3xtTw0vwWDWvFTjLEi5pvhADcjF2IwSWEJ94inHskY/MBjY6zFQKWKLtqIIzKh3gErs6R9m7m6lY0TMEt86gw01nBXnCJBamuyILfS3UqYK1du9a7DrgquhxjOspQR0c5p+Z6j3mRTIVAWTTutuNIdpK8GFu7PqBqa9T7G8IricrUwUgdo2OmZQYDhMBhglYFJmEq2xG0XFlC+JTx8YYkaATshExqA/0iK9ILXaxYWX1KkJxpheTyfmhEFRxm27ZtohKdOD/IM0KsSomXeqoWrVcdfcFUKeWlF4coyhHdBaDkT7B3VOai2vK8tVQjKbT4GwhJVOcpTfIE4hXvtWAk3RojoGirysoHfWIortuMKjlED/DPrtpqA74uSg0sYg/IbjytbTm3Vqx3Uq6jAjOU9/2g2j6npWtx0aLCOdUnvQqQuIJGiCKVVSXpZCCWogvvjQT+iXZ66gqsoApiYI0e+m+OTILiipyklwC9yO8psUUEMTXj+0XmtxEctU2N+qZQgv8LefZuCVfxwvmBMHQoWCh65YSYzAyXXHKJ8eIInZsNzWRCns/WXMjgTNsv+qth2pBPvpgtJ3FkcsKgSldtnRqeGimqKXVY17YRQkO0k9Hs0245K43buUhZnFVaZtZOqvXUGDFAGlHweTcxElHMicfmp0FrWZHxIoAwQ4bASAUiWxZSt8A2QXnfBRlA3WaTjgmD7hZ1jFZMK41EMjbDePla3kg5UZ+WtQT4xDmSGGxf8RDQsFOTGwBCWpWgEEJ4w07ttjm7ocmYbsAz8aj64K9fTF7ahGazQfsS6elfg/bYFBKhPWVrOnFMpygRKu/yCmEevw2jBIdm3MD+c88999/+7d+iE/NDxjfEUFxXphOI8fAAgwmBFrK8jKat3JShECIaJL1iSyxBRFdv2YlNcl/HlBySsFmAzBX4ugOfSYyJauIMtu2qQOTxZubxzivcRopMXCGAuMIlSMUbsU1WIW0hdX8Qd1evXi3SpERxLsR10vIuvARGIipXZBVS5S1VtTS9detWh2PbVAeTh2x0ou3poeJXHLwry+unNEEB1xGC/9iySayYSrohiQfmel4MvarbvuCV6i1WYKl8fMl8zhtI7Cm7MAfoccUQocEjbesarBDnBm7xYdmyZWxNIWynIVKYECiZHcMoXK8WbYKhuM6c+ZEYIcquScCDq666SoPlsCSdIwGuC42UdcUVV2BP2TsJTEhCzqDhtrl2MhJTpS+Ml0DMwHh52gZU9tTBFE2p5b//+78dpKoTgrfYkosigRxlDM90ZUvz83yxTWrOiiK6kRRrUVxPMd0EfRm+grfyorQjoKSneNIHqlfMIH+SObWcWCDKJDbhcT4mooQiuP8vStg4I/J5g5kSy21cP2XSD7PqUS5SYM7NwJoY5SCkXcjeVNqhuC5kfv3rX1chcDUbEOrqVVoFkmE8y5111lnR5vDAjzPOOMNyNOKQXvZOwP4FSxoUEkQLEv77v/87W5aPp0LUl6t9qTE0sFMyVWgKya6SifyTnwqBeIPcUha337JlS2UJ+6Uc9vYWy7GZakesYuDkNAmBckxlDFE98iJIHVKi+e2O/OmcJmSnWNVvOAgyHlPz6WdYTl1UQXW2TF0UwgFsOZUbtfBzerNBFuQJXoxXi9beEl9UMibpWKjQGG0nrmf1XOuNyRiK61xT2WqHiEUsPppyOX6cekMnsTw13iNnRApldanKW5CSQxJPRmuDTo+MMVLWk/64tcrSmViEMCd9qQVRXzCoKhwLYbaG5MgftNGUtIXUU6PSF8EQLi+K68wmKqciYi2s1cN+rCJvIC7xRLVs1tGCnMYrTlzZj4TMyUn0oK+YZy8YZn4SYgDG25SyJ59XGInoYiENt6QpBOthy4GROUklVfY7fyFR6xWmT1BgWbGZ8kU3wZhRPLI7aqETWUvZZi18pUD2EiCiJVqlMWoxifHalGzvpqJbBa3gpQhEAy+qh+ur51pvTMZQXLcNJGPsW265xcbSqUrOZoR5sDc01R8h+DHarVixgpNcXPyunQqE3Nu2baNo0RHcgoYe0M4//THeWyo5Mwh71ZxWtEqWoyk00qMfSII6/AH7qS+dPdTRBiEnVX7HVzAb7w2g+oQf1soRAmtlM5ZGZeynIu5avNSSVj+rq3DMkE7zc0vvCnv8QRWUT+tQgYdcc801I/nAtDdwlLZjrIao1MIcHF6Dlwo9orggRecyP05njMGYqsyzd5aqFsJdpqElb3nE3K76+TnFindewXVBU0QIPFqyZEmlk4YG7Zvr5g20GcahQcOxkslZBcWLUR1QvUV68QyNGBULzXBZ8bu9rsGlxS815prfhrQ9I8Fb3jVD5uwB2kQUUkXX8mlVHkSMHqgG2OCmTZtCuwoIqvAQw+69915bqEj8m9/8hsHEHo7nZMLlBCS7Y0tPpQUx2yvxupQxIoXDKCuS1hjriuIYr5/k/IdXCGzeggxorTRSVHNKgGjattkmQGuBKXbh4ZwW4/mnKiUDkDVMdU1KlMqSe7VT93rFxvXbsiRJXSQhT8eqGAwWR9JuqJah4rpwlQrPJtmYoIIoVmFAghZBMSCDu8ErDGy8wmYy9FNKLN0DBMgxnxiCAaKgHW65jWMIMKTK4ObgXU6ZoohqW0bCTrszM6KrQctBE7oWtOzFlulEwkVitswALipUC04kTE8F4U1Q9CJ+Zx51kRjJW8Q5dCc5MbzesXIdISgQt1JCNEQEJn8+fpDnaUbN6VYBpsecvF3ap5AqKAS2g998Pp9r6TEbw1EyJ4nq+HlFaPUkDRMv8zi1s0geNcRQXMcqhtdgJ0U5EYV2G1OWiMcoIrCJYeIrLYAGfwj7XcGGFcSYVMzXGeKEgwtFGJx3adM8mRNMaxVrWTG/1kAGktCjYyJH8goypYzuC+K3kkmYYTD5XQPzLMSLxBXrkiowWAJhG+Et7FSzZhKSWD0/B6iQVypwSGUr++VgR5NyWmYwJ8lxPflh+sDrnAjxqbxvDOU4gZmGi6IvgdXf+pmJIRgow6C+66otfksCiUT0gA9iqL2ThxpFOuRWu+MAo/MEbUqmf9bJDA0xFNeFq1Rp+eKOBjoSUfrmqVycjeUsOV1tI8TKZWyGjiIf95DR8Gbp0qWJAd3AfdevX49YBnvFu8obShTyzZkCHdf1oIt13dJOoqDcmqrOuvkyRhvJekNQcbSwC/FGEYltOUB7VOU0yJysxRj2yzFYImWJ2MxD8ko3VCJhSQ7cWELgdevWYV4MbC1qzLBpgpxDXW3Rd0oQXmC2WfqRfrEQWaN8JstpNcOC3AZVj0DAt2MpM4TZYoc5+b+IHi5JlbTB0MxRfeOgbc4e6Mr1JlNwrHDdYDucXCRQnHSWDBUg/fz588UPg1OzLl++vDfXRTVcxy3jObr4unDhwrrhzS/54kd5PwG1YBVcyVmt0lw7luaKjsI8s+wqYAYaV2yknU5mUKVwSItibeyN9MknbqdcV6kqNSW0ywaLFy/mbJQcD8+JzSQNUUzZQnnfE4aJGoJI2unsBiE8V7zk2CtXrmRosZwGiI3iZhAXOHmyhMGu3ZCnmEBpubVZdJcrlMH0nGQOyI020iat9kt06MD15u+LuPaTtl0Rws4ZW9pSOZBeuNVp2wn2YBgfZUWEwEUKEuapmI3REcSzekP/5s2bnc3RyHg7X7RokbxmnkyoPja5fmsJitalbjIorKms+nSIK8oqGn1pR6D6l3/5FzObLVTmbLjrNgG4DuvyOrwkMwn1CEhslvN6E64b712b0lYfcjM6VOmKeZRAk1QntuUkkOuoYGbqUvJpqEl6wxgaFllXr15ND3Qr0EhoFFX5tiDNfNly743nKbdhxPRYwpw2yMp0zsmdjlC/dUIv/rpbNW0aDTFUDSNSXnjhhXb4yiuvyIA4JzIpvlULrE4m2xaZsJbJcaWorn9EKdRqM/TFltCm37ZbvDGGTtHIW/IX0iOWqczpwGB+C3E8gdaiyEFx+VSLVGSzkLI7HAqiJijvv4yqn5yykA0KJ9xYPgUmwXUbtKKgCxxAOacAkHnzK0XY43UBCUHr8biYtStIay85v0piJpTQVAhnnnnmtm3b0AhsnzIl9N6YPIaioLz5MvLIK+ZPW6fbOjIStKmFBjQ44bcK2DhRZT9c5KXkl3jpITmq28ajE9DGmSoJ26OpqJrFKZ+tXU2bIifIW31hKK6LlPkFPOzESDIJq/iHl4Cg6EhoSjn33HOT2kYLupg3bx4+mdxaNJKliZGlSaWIF4FQNhyCKdVUDZA0zMCiKjHWNaFpeRq9izf4zcdU5NyJ2+M0ZxPqDCBSPiwjGAE0mtiGz2Bb2kijzUXplmJzchXdKdzhO+GARxVRfgoU0bAR+HMTKFqWLFkShQAr0BIVYQK1kJP88hi7p3xtsndepEzVEKFMlYP45Bf1NJmtI4aq19988016J2I2zK545hYVhHNhj7MKtGyWBMRgaIEflOJKKeKlTMfGQoXxrqoaT73uqTH0ZTZXPZ4aKfKZ1jxmY2zLCQMIp9MMhhmvkyTkIRVwP3ExFd4A4CTmtwW+hOuYB+bkUXq4tEd4L8CLTCT0ijinU4PNWM7TYqYOqIynXDYyoYvbUILDmauFghSHUlZVv54ofPrpp+ThRZScz8tlIeZgJhW2sjYfXMrwQntVWLdVccW+y1uvM2tO8Nw4WXHkGIrrqigipq0yZic99iydsS7OsTelxH4sxMtzHk1Zwni4Ij4hje2lzZzpsecoVFsPtRrv6t3o12w5/3E5b+E3zxFX8u1niv7FL35RxXJyDvBTkm6wrtr6k08+UVDmIwiFky1XH7rL4KJANs7Y/JnjdfuIg6qJjSIc2K3aV93Cz7UtJD8EvT/MmXmgNQ07jQAlKF/zuSorcNHwxy4kVTGoeKMDRHGGlgajHAFL5aYzT0eLoWoYps1HjXUIPPJReTOBJp4zGCbPLCGkRK6Al9gzJNctFJT3XVANEJV31/5UDr9lUf6M0Jjh8CD4UaA8trf4yWiIkpLdYO14ch3F+tOlyd6oL11vtyH94hErhLLUrtoBPiAAcQx7pwGhShSjE5GreLVFdJktOaHb/MNglFyPfGxcBfvpQw9dU1/bh5iJlCOM61MiNBXXpXUW5WxuhS61UA7WqXQlN5GvSj7Y4FyoFtK2kW57nOXI3m2TzquITvnKS2k55S4NyPDcPvUYP1cOCJEhuhn63XuT8UNxHa0nx3WRbHLnlCgs20J5PxW6jaQmCbQtrhtMpOn+WUxQbKIlW65YjuusKKKTKp9L1GEYA4vuTiNKtXxIWsxR/kJt2sXYuYGKqRTuIOs0pbpT7OVpHWIQtkQ/qB/eT99+h+K6zWzfvl2s4rXawGw5qpcjpsLIN2Y2q6v+I490STaRcteuXfny48hXbAInVPFbSFObyu9qdwZ2xtAmqquDTUc2zFHUNRxPdoISuUV0u+b2UYUeqnjhhReS96YbU3O9GzPSf/jw4eJzrfLnGhryMn9tEWrGKZVFEQuTVMCRSkO8dIrKgIycSdQXFbq4HFrzQ06oepkZM58Q2Hj9IyM7tV+7FoCc19Ut9aczYJqh4vrAsLGgvB8Oo5pnjLmCwSw+h7leTlGg7CqQ27bOATD8DNOE1oYLlPenHgbb+4nh+kgQewdlV080Hwl9Da7Q/K0BJq+QdyfPMMycJxYjlLzHVF25fmIV19fqDQdXw5qMH2z73hrsxX7RcZWZWXo6MELJe0w1Gq4b3G18X/MMhoZLVMOajB9MbG9N3+RzFE10MkKF9JhqumqYbkv+X/Hnan/5y19+Wvyhoo8++ujDDz90KgeNNqQ/cGuww7t3zTDMD8xHqNk2mHn4yc3w24l/sGinX0z8DQV7j8YqREvlTfEHhsAwuoXPP//cuyYZXqSTA9Nbr7PEc88993Dx5cf8wOy73/3u/cW3djUCbT3pnIw8Au36eI3vFcjkL7zwAmOXq06FhrZv0bZPltTHa+eHQcePHyfbkSNHfl782wl4+eWXqeXJJ598vPgv6fv378+X1WzkkQL5wardPTgB+83np/fcc0+usHfv3vvuu++u4h8mXl38cfCdxV+y3T7xH3rzuwH5EwybNm267LLLdu/effPNN3vRnPnJ5fvvvz/5J1wnJabmenN7V9YFtmSqa6+9dsmSJXSaz5JZvRowJKyVz2uPHTuGKIsXL85vSCB9OWJoWALKmxq6daafADaOxD/60Y9uvfXWNWvWLF++HAXvvPPOyl0BR39a/G3OfMfzuuKXqfEVTbEz2Lhxo9dXrVq1evXqDRs24OuWAh5hrevmzZvzp0cWLFhgZP7TdGidP0xy0UUX0T8B6EdDD+RP9l1Z/EkSM2hzAPI89dRTR48ere+u2tTJgRFz3VXqvPvuux966CHhiuqFqGeeeQYdAekfK37rQujKDw4DY8AruQpprrnN0wwDkc/rJsn3SM1plfxI0lp4I3UIe5yqkmcGUC1k77t27frP//xP3BVugYTcHq2T2biB7eA9FvLSAwcOaKxfvx7bEF3EpTq0M9I2vetFFDTMHl8s/v66SJzrq6+++vrrr+/Zs4dHXXPNNXfccQeuG6w/X6X+WfFvvmUPiqIW0xpp/BVXXBFnkAFuu+02K+q3un46lCKMf/vtt0++YD/iGkaByMxsg3ann376jcUfwWKYN954QwY/fPgwJb4z8V9QgvwYXybNNY2gahtjpDIgyDdIzfZm8S9TzM+d2Pu0005DrH379onxMdUwdPdu79frA9TTKIJPgrRa4vbbb3+t+L9IyI1nOEdUlYy0RkU47Sk5zz77bCN5SCYZAJidr/XzeXmDb5QPusNydMgZjCcqfm/bto1IiiLFIZ/hA5IGHfJPhqun4mrL1cbnEEbMdRFL+sZRSVOeTcUpSlWxWTgPcqs/SCDviHJELbrXZzAg8dJC0rd8jVLoRRLyVLYZAA3fxV08QxFEd1WfcE4UQWIiYVU5roAJBVGeievC/9q1a8sHg8IZVKjmVEpwvJ/8beopIY4wGWaL6+p7M0S95I8nCPmyqPA0275A3y9GwPWKE8K5nKhBcWKthhDCGNUHCPncAPK5CuisI8PqaBvjtu3d6pOHREfp+5biV3TZTBLXqMQbIaoJZS0k4285OKI48XJ2RI6MiQCgzQm5paSkSv6nf/ontVzbgAEgedI8appWeBZodE452+QVkV7BQ3tkU8qL+iguUfDhnHEtYYBh5QtzDSPgenLcL4u/pIN8zz//PI3n0cyD/azO0wRXAYkPlA9Gh1DE/Ini6hMejhYCvPiKK/wtI4OCVK1XEEWhxSeFdofClOwZ0xF5qw31zrT5lXIINdXx5MkvAXZ8twm8SCrVi4OHcpQPo7ssRHi3l19+uQrHOcRa+VWMYODlZhIjq2FoXJmhsXPnzrZflZhhcDaJRUMQFXHTOVowP3tjlS2L684POpUQGzduPNTlb5i99dZbYgF+5JcYMAbje7tiRwLVO9POWd+0Cg+Oh/TVo34hbOVFMctUTOmoqkSUqWzWNh26bFltg/Q83Lp2PVdOsY24bv+V7mjB9l4s/g2GnTuT6aSFK4v/bKZs6Deom000EiOdsWRJDdeqQaGq3m4E6gYx6aXiT8WSisB6XElLZpJbUdmjs76vbqiPwVcRFE2FZ3Qnm2ueuh4/ftyijneOCq4oiC4ivRDI/SQZx1PZgGxmQKDUG4pgciJZxbN+YTvKDKtwHid4Ug1GvtY+f/c78hAMlGeCBf8UzmWhFStWOMJyJ0tITdTIXsqnu+++m7f/5Cc/UVWWE81W9MF1GrQxh841a9Zs2rRpffF/ehcuXIiU4qhOWlA4srSzFzPjATi3dYRHTzzxhPPQkiVLvvWtb+Wf9C5atKj4T72tRmD+8847T7o3Mp/Ble93gqdSLSMxFXo5F5qc7fmM+UmbT6mJakLZ+TfF368qN9kFGaA+YfhEMi8+Vvwr51QgBoSmGQkU5RH/fOGFF+hBxDU4BxiKUgqjiEgpOuIoTzYz6rsGBlc/VOIwXudgtiay2hp3MhWY//Dhw/Zop1zarZkdEiSNgWlnC3SS6t+BhxjaFnV4dQKmN4V7whAl6LQFnTbFDaiaGE4jzOrcQp7XX3/9F8U/yREQB3O/0aID12M2KO8nIP/OmzdP8tKu4lA+KnZwETIFMyqQ3JmQbRJB7dnOQVQTWQNtPfm40K0xzlWQDxNdA5Zza6Qx5k+jDdVUyTYYj0aJrNKFaIfcJGQD0lbk5qs68zH8lMAnzsOEtlx2TaClpgmuuxWnMS9Jz9bUcpwkI0E/xmSSXH9V/PfnQBsnMMOu7V0OqbaWzCCCVH8RJD+mwEVFmp1yHlfWEYMFBT6pkqm8yMgEAjMQjLbN7wwtubEpJUz2efLIPwaX9wU+/vhj85uHVFiuvKF/0pKTilwJSYyVK1c63QqLfJU/SAUkQQxVLqncmsGO7NSWy6lraJNkhGga12Mb9v6P//gPHi/G8HjGw/Jly5bl7E93DGyH0bJdJRKzkFTOSNpB1Rb2tHN1W0TnllVyheppEoVGhdyKOkyoTbYfFb/VhuKsS5LUV2RDa5YjLUlIzkjf+MY3BMtszbUb8lQYw6r0YAaTI2KqoDpEXBEukZiNXb2FHJiRAZI+jmqYlqVFBB6IrF4hPHnQOiMHgzKJUTTEdW6WKKOBWDKM+a3OOvKGRRmIoiokn+gns2G0xH/OOussAVsZg9nSBbsLInRIt3Suf+nSpYJRVq+AKkhva+ZkRzFIlmMLfmXX9mhyyrGWdQ1jNbKxDodpGH0mo4mH9FHDgDbd2eRll10mna1bt+7MM8/EYxpEevUic3LfcF2DahBFp1hrk1QfA1R/V4SmKMIm26AcgqptTOK9V7xrktiSHq0ijFkIrGJpwUPDurfccotCE+0w4Jvf/KaDI5nzR7zCv2pT3ZCniCKbCVTKdObJv1FmJGTlz7bPhIbFvYv3/gCrC3UapkKXis1CJjUmlvNGWxMXbSRk7YEeAuMiz+eK5kHccBcIRoxy0JdhtiqliPS0zY0pFpuRDykTmDOJCfkkiDvkZxQrYqp5ktmCzAzCgWE8h1EowZVXU5ed0liyiqu9s6M5sYhWDUN95RnByolqu25boi/04nrmhfpt2gF60YUNyFBKYQywc4FBGYP6nlK67I8iqnw7LF8rHMZb3qVN43m5AGCHdaTH1TBaSDDwFkuUsxR/WUpoEc/QWh2Jjlu2bLG6OcG6OErL1M1anpav9QnBRpATyZQHdiEt6GFdHMUJrqVOVQawq2HoIqyypacafINyMo+nLK1BjY6A5GFa5LY1wRJNbQGfMngAULiprMul0UgPqRRReOyRiJNhI0Q+jCpveoIYdEJFXMUeE5KAfnBGZ8oEKYiK2Cs/H2T3KpQEbQzshoKq7SP74DrUb9VqGPbRRx8JV27JjYt59OmnnwpaIrEdGsB3WVGZkaciB3YKvV7kvpzExgRaMW8y9OOKU5e3kEy0RizRPVNZce/evflxkrWE/J07d+aHSkBlfEN4QDJjLFe92BDZrBmsq0Tht7bDpXEo/VbkS3ZnJParqViImzGSpTXIENoBQkt9aQOB2b4CS9svL6o03C8QWnTQsLSIKxID3eIWoqNXhg2PSEjUHTt2yLpVT78wA6pwTlamXm6TNEJ+euMAtqMMTmkq8KcobQLyTBZp6hqm2zYIIVgyGMazOupjcDXYUy6bNvAEETrtvOJdtHCLwcJ8HnWE4k+1oMFsPEQFLChmIepgzmJUC4yNbWmjo5n5UjzNK/QVGbrtaDKMzGCinnPOOUwrruNrPn2zKWZw9iVGxgN7GCNIs1O4bozXEc4rzjbCuadoTbDynS+juXgV8gp+V9s3uRpDRAQNrp7+kSDLicQJcwGzim6MRcnIatcpflgtLsf/xTtxWtjqxlpxSjwS3emNf4pcCk5EEibMII3glYWMbBmmT0U1qtc7wmZ4oVLPhvMhF3MyZI6SNrxkyRLU1I9nwny9hrFhtUeKCoxUrqW/IxQPmQdlnfGxRDtnZTUM6rcGFU+XL19udRAh6IWu9dOdSEyG0E7PlDqKHiGrKCiRG9fpnTuZRDJJAcYM8lI+zbCuFMQBLMTk9otk8oDQxbSuCEdRZLYjbDBYTvBWlYiGBFVIDuXNNIPd9+zZI5RoMx9nthcEoBORC+mr4xb9Ax2iOBKju8GKW54pFlSkpwQWFNSoV+zAn1R3/CSf7uMAz2GINWvWmMcrDJR3G2JwrluYQNYjiroiBua76gr7SQHKDVCZg9ajb0REmq9//euCH+ntirPaLeBNGkAR+u1cEWLOr371q1Xt22JiAWzjM8yM0FzIujzNKyn1XM3v8GCkGZJqtDNJb2QYTtuC4ylvQVPRhQxShKlMaBdsYCESspzjnVfIrLZRluAxbZCB10UzZNOPCvm4TfpmWu8yamvJiUNe2n1BapV2sKq8n2bYPsl5sphC/wJQxJZG6IrSkrcpyjVxXadCxZYzg/bGjRvPOOMM9SE3IH+Po3m4xJMT3fAtYagv9M31yhICmPikIUQxuTCGYfbD5LRgb7zcJhGFoIZVL2qkbZhC3EHK60ZS32TQFF0YZnvoUp/BbrNhREF0yjWhRQ1L8SDfCTxe1zaMKyY/NAcTil7hZYUIDyZUtolPLMoz9bCf8EZmSwsEyJ3SUwN0EpU2KMowbyWhcW90ERFS2dtUtURz8J8ckXM7wAxToprTNhVyPNzpH7PTKcrka3BYgZSUo+FqmC3rcY7XYCBWqHzb63L1rl27aANkTrGA1biESEfzfECUMdKLVK0hPTo4SaTFBH1g8LjOPPaswcxcLWkloHEikqau+rr2621gXfsxOGhrtw2uUEz5h0cGW9G6GmXXxN9EToVH180/i8jMTFJ3D3Kio+zMivyTaYXSHAd1Mj9LO7dgMGfzIgdG8dRUhnHF1C1oIf5hpzFEEh3NYKS4UBWjrfWmQqGAFrQtUT/4TgeyEAoqo/ktaROkXS2Ni46YmC1C2Zdgx6XlMdmPt+NxvjFFOZKPHnyV/bxOdWK2sEJpMrByV7CgSfUMjskbSR2uiVnqIl6dE0i1/SYYkOuszkgSSm5Zzs7JpyyLF55YkIEeqVu8z1kCSNucDdEg26xatQoL0dTucFc2Yzbpi4UYg3kME71YKyznAAZodNMDXSEBy8k/PFMYkxzyeT+Bza/R3H4VSEi88mZ6EKmsgsqQs4HyQ7ilAbS2d3TsVkd53Tbt0d69mw/WUrTIgTENBpvB5FzXCUcPFfF/19YUxSS4ZxLpWjtIfwb0wOBcV12F61mGNORmKi5oz5wYFYSxRLLpRlaxonWBACQR1BPjIyFpb5n471NN4C0MRmsBGBw0P/nkkyp1OoExlYZpDWMkjsGKok4GoD730MNyRKIWVypKoWJyBsZyzDCzSCk6cg89OfA1MV4dHEy9VN5MG5LD5SVUwwF0FE3CVK6bYs+V5llE6iuscQ8lEK/6tE30ickkNFk3dFemVp9e6E/g58BCQzrr4DOXXHJJHCCKaqKuoWqYeHbbMtQh5iGHyDrzCClTCQSVeGoYJ/20G4IJ6x8fVWBm22dyDSdLUdytzJun1GIhzqYYjTCswk+wn+dgiZGR0ORKVeRWFCGNHjmEoxbT9AeVA86VN9OAqNFOcdcuRFa3goud0pLs5NY2sVOPdCcE8H+hAfW5sRLXK2oBSmtNV+zUrdM893YVKYSGPAq8SJPeAkmDq4gL5lQ7rFu3bsWKFf1WEINwPdtmsJSqcwUKx34rBKEFHZnHi4wBLCTSiM2o6ao8RU39gndeYRhvVdF9MlhIAHYIS8RiOW1FsCzh/KOhGE2A7Au4Pq3miNIskc0SFfOuuOIKOU0Q8Yjkihnb7yG8gJ2Um1vlkOguCWiYsH6ashz1cir74jwigrX27NljacFi+fLl27Zt6/d4Onhct22ZSENsg3TW0ZxSo0LHFatOWkb3es+UkHwffvhh5qRxpzGK3rBhg2SNoCmHmIHPK+ei95zYmsQbBlYM5NObfCIptJvNrTiXZEKrlagaFdLTBkWCSTSqARlcR/orlL01lA86PQL9RAV756iCt0Ii2cxTuupRROX1NOSBKoTbrPDhXWdTMaKuOplh8+bNkqFcTSGSpJGyCtYJB06xMxHXA27Nj7Meq0guzhPUzbNRSo5uqWeSfqcb2GN1qqR38VjlEBnIqTRMKG0ulU2JIkK42Ry/BLB8go6XzOMwwFSqF+bXqXhtuESeejG+l89wnNWUQ25dL7744m4nvG5Q4E732RToQZXC+UVo1l+/fr26UT+mcnL7arJxAVuQVt1p50cTlInTuJ6PpAKmpN581FOBRbZu3ZrfiJ05rpN748aN+J1bLF+2bBkvxwn55Ynaj81nElzfOY8MIvGqVasE2vQTj7Rp97bHZJgEL5WMKhM+TMWqT4FZ1pZb2YnBDNMpwOeVJkt4S5TS4DMipVI4SVywQCNtUAdL7lPCu3IOYXij24Zv9YBCOSjvC5gcHSUf7EREbBN3c0CUVfiAxpRczwBKo08NGVJ+oze61RkHqEDJVMHtHWc9EhTwzer5bZv6h8tNMDjXYe3atfiU9G0P5EY10cW2+Z9OQU5b1SUBCQAkFmhBCAyq2zSCPKqjfFCg6hEDzInWQpoiMhFXpzYZOBt5olmGufDCCwVLbUhnE1Qj0VqWMDMXAqaVOjjA7t278TU/T8GMFCHQZAnjzaARrkvZyMqvRM1FixZt2bIFk8R7c3rKwAHbB+V9AbfGIGLVXwz50pgmkMHaIFrrJ4bJtVXM8+bNW7p0KYfMz8sTXNUVrFBsqxF4ZsIQipvWlg8cOCBEcgCcCZiS2mUSPu8Q7JV8sseTjT/99NOT+ppbc3Cuq1volxxxyrK3BnFudQFmU11JPa6Q777XMbmnGy4v/kyh8dU8NG5+LsfxOn5motpDF4bvK+hWMLg+3q6dvTgb1xJmeBGrp07VwwbMo91ticwG2nwm/BACkEkZIDE6p4pzSO+qfMIDMQwJNPS0QWcbdCJoG3FzG/ZXSA8GTwazApGA0sywadOm/LoTtt1ZgPJ37NihxgjhRByvtHbYAHxb7lWzacsVXrRBq4TNiSYKBAGLblMVCy5OtMIKGbwrRWvP3NmU1WnWVmU3UpJPKMVvHqnzqaeesgE8M1KaU43JR0Ij93Wt0HbbEHlLOjPn4Ykv9b/xxhuor5/jyXeUKHJQkNgpxYuaqFAIPmK89tprSBP6Mg9VpL8jKA00ZCQxMj/nUrWzJY05+JJZbgTKlEaQSTThw8rWhkhQHCGkR8dQe0RHZYYjvj0iej6AyiGBCThParmOiH4CPDZP2qZ1+GEdk2O/eMGgbMeyYrxYgOtKA3S3livQTMegNiWG4rowlqOJndAI+YgiOImj5BP28qgYPr3IKmSwrmgkzFMNeaqEwwl5ZtqjgkUD+8VOPalGhJy4XzfwPcFJINeWIsRyQUFVhvQcuHJm7O+YME8UFBViFi4KK5BKksdSu6cCnLYAn8EdQVcMJHXkc0mb9UrKbjrRrxZSHGIzDYih4ogVEUlJ7EQrkJuhmKmFersJhjqbYk+4Xof8m2K9CfoVtxvq8wjq10z6B6tTcn0YSZhc4Y6aueVm2L9v3z6S1D8rYC0WZU5Pww8FD6lSuTbEqDQ2Kjh1SD5JULKKgC3qoylPyAAQFgVszPZUBksgYBFUySlrujeV+UfG9UzHX21A4kvnlJiOTYqRuJ5SocK0ch24tyQuJsWQQiCuK5+UtsitwBPs1d8yfvWjcolbYszHjlZvKMCQcg6JNjmjZEdz4TkHdBD4ebuNK3JsXBmJ4m4ff/zxEMMM0iAbpdafAUTmoWoYybftQyKd27dvb+vsgbri+kKPF5V9VF/eTACxJneOChFGCJeglddYjut5pFPME70E9fSAepQDoH7OZ14P8rQ3Gg6bGVRiIzFm5yOpKpV98cUXNg7VIVIeE93xXnR3rNcz5Hb6en1wroO92aFApfQEDScJGS1f9ugXfcndcbBOoFAVhWONgoFIuSrixZhy3HQCy5WbIrpgJrw5riltnaVyVv7Od76T6E6qjO+4kTkKtbUt26AyRhQ/eLD1l+HsXZ2DGKiC5Y8++mjzUNgcbWosiNCu2KG47lThDOGQbgOgwZz1ANYXJgvXAx0HFxts9SugZckIRssazqkdX5k+sCgb79+/Px8m5LyF922BYIalmibUd6GKy7fZkCHEoATx3jm7GqZRf2Xk6Dj/1FwfWKaBXxxjjOnAqcL1EyWMdWeVHmYYs2r7Q9UwgyH7D8qu4dBxnlFNPocwq7bcQ5jh5RxshhPA9ZGj485nleFnBrNqyz2EGV7OwWaYjVw/BWk6Eoz11huzlOvTarbpm3y6Je+Nk3VffaGHnGOujxLTLXlvnKz76gs95OyP63Nlw2OMMRknw9l0jDGa4FTk+gnMTq1SoEB5P8YMYhzXxzhVMI1cH0evOYRTwVhjro/RwpjrQ2HM9TmEMdfHGOPkwZjrY5wqGHN9jFMFY66PcapgzPUxThWMuT7GqYIx18c4VTDm+hinCsZcH+NUwZjrY5wa+P3v/z/x5wd7trGHFwAAAABJRU5ErkJggg==';

    function genererContratPDF() {

        var doc = new jsPDF();

        //*************logo****************
        doc.setFontSize(9);
        //image logo
        doc.addImage(imgData, 8, 15, 30, 15); //x,y,longueur, hauteur

        //*************adresse****************
        doc.setFontSize(9);
        doc.text("94 Chemin de la hache\n97160 le moule\nTél: 0690737674 / 0767321447\nEmail: joel@joellocation.com\nSIRET : 87868990000016", 40, 15);

        //************* contract GRAS****************
        doc.setFontSize(24);
        doc.setFontType('bold');
        doc.text("Contrat de location", 111, 15);


        //*************numero de contrat****************
        doc.autoTable({
            startY: 20,
            margin: {
                left: 107
            },
            theme: 'plain',
            tableWidth: 40,
            head: [['Numéro de contract']],
            body: [['DV00100']],
            headStyles: {
                fillColor: [255, 0, 0],
                textColor: [255, 255, 255],
                fontSize: 9,
                cellPadding: 0.5,
                halign: 'center',
                lineWidth: 0.1,
                lineColor: [255, 0, 0],

            },
            bodyStyles: {
                textColor: [0, 0, 0],
                lineWidth: 0.1,
                lineColor: [255, 0, 0],
                fontSize: 9,
                cellPadding: 0.5,
                halign: 'center'


            },
        });

        //*************date de contrat****************
        doc.autoTable({
            startY: 20,
            margin: {
                left: 153
            },
            theme: 'plain',
            tableWidth: 40,
            head: [['Date de contract']],
            body: [[getFormatedDate(new Date(Date.now()))]],
            headStyles: {
                fillColor: [255, 0, 0],
                textColor: [255, 255, 255],
                fontSize: 9,
                cellPadding: 0.5,
                halign: 'center',
                lineWidth: 0.1,
                lineColor: [255, 0, 0],
            },
            bodyStyles: {
                textColor: [0, 0, 0],
                lineWidth: 0.1,
                lineColor: [255, 0, 0],
                fontSize: 9,
                cellPadding: 0.5,
                halign: 'center'
            },

        });

        doc.setFontSize(12);
        doc.setFontType('normal');
        doc.text("Location de voiture du " + dateDepartValue + "à____h_____ au " + dateRetourValue + "à____h_____", 25, 40);

        //*************details locataire****************
        doc.autoTable({
            startY: 45,
            margin: {
                left: 10
            },
            theme: 'plain',
            tableWidth: 95,
            head: [[{ content: "LOCATAIRE", colSpan: 2, styles: { halign: 'center', cellPadding: 0.5 } }]],
            body: [
                ['Nom ', { content: nomclientValue, styles: { fontStyle: 'bold' } }],
                ['Prenom ', { content: prenomClientValue, styles: { fontStyle: 'bold' } }],
                ['Adresse permanante ', { content: '' }],
                [' ', '33333'],
                ['Téléphone(s) ', ''],
                ['Email ', { content: emailClientValue }],
                ['Date/lieu de naissance ', 'skfjskjfkjs'],
                ['N°/Date de permis ', '231312131'],
                [{ content: "INFOS TRANSFERT", colSpan: 2, styles: { halign: 'center', fillColor: [255, 0, 0], textColor: [255, 255, 255] } }],
                ['Infos arrivée', '26/03/2021, Vol fjslfkdjfl'],
                ['Infos retour', '26/03/2021, Vol fjslfkdjfl'],
            ],
            headStyles: {
                fillColor: [255, 0, 0],
                textColor: [255, 255, 255],
                lineWidth: 0.1,
                lineColor: [0, 0, 0],
                fontSize: 9,
                cellPadding: 0.5
            },
            bodyStyles: {
                textColor: [0, 0, 0],
                lineWidth: 0.1, lineColor: [0, 0, 0],
                fontSize: 9,
                cellPadding: 0.5
            },


        });
        //*************details conducteur****************
        doc.autoTable({
            startY: 45,
            margin: {
                left: 105
            },
            theme: 'plain',
            tableWidth: 95,
            head: [[{ content: "AUTRE(S) CONDUCTEURS", colSpan: 2, styles: { halign: 'center', fillColor: [255, 0, 0], cellPadding: 0.5 } }]],
            body: [
                ['Nom ', { content: '', styles: { minCellWidth: 45 } }],
                ['Prenom ', ''],
                ['Date/lieu de naissance ', ''],
                [' N°/Date de permis', ''],
                ['Téléphone(s) ', ''],
                ['Nom', ''],
                ['Prénom ', ''],
                ['Date/lieu de naissance ', ''],
                ['N°/Date de permis ', ''],
                [{ content: "ADRESSE EN GAUDELOUPE", "colSpan": 2, styles: { halign: 'center', textColor: [255, 255, 255], fillColor: [255, 0, 0] } }],
                [{ content: "", "colSpan": 2 }],
            ],
            headStyles: {
                fillColor: [255, 0, 0],
                textColor: [255, 255, 255],
                lineWidth: 0.1,
                lineColor: [0, 0, 0],
                fontSize: 9,
                cellPadding: 0.5
            },
            bodyStyles: {
                textColor: [0, 0, 0],
                lineWidth: 0.1, lineColor: [0, 0, 0],
                fontSize: 9,
                cellPadding: 0.5

            },


        });

        // **************** voiture head only****************
        doc.autoTable({
            startY: 102,
            margin: {
                left: 10
            },
            tableWidth: 190,
            head: [[{ content: 'VOITURE', styles: { fontSize: 9, cellPadding: 0.5 } }]],
            headStyles: {
                fillColor: [255, 0, 0],
                textColor: [255, 255, 255],
                lineWidth: 0.1,
                fontSize: 9,
                cellPadding: 0.5
            },
        });


        // ****************voiture body only ****************
        doc.autoTable({
            startY: 108,
            margin: {
                left: 10
            },
            tableWidth: 190,
            theme: 'plain',

            body: [
                ['Marque/Modèle', { content: vehiculeValue }, 'Carburant', { content: vehCarbValue }, 'N°Imma.', { content: vehIMValue, styles: { minCellWidth: 30 } }, 'Kilométrage', { content: '', styles: { minCellWidth: 30 } }],
            ],
            bodyStyles: {
                textColor: [0, 0, 0],
                lineWidth: 0.1, lineColor: [0, 0, 0],
                fontSize: 9,
                cellPadding: 0.5
            },

        });

        // **************** etat intérieur****************
        doc.autoTable({
            startY: 114,
            margin: {
                left: 10
            },
            tableWidth: 190,
            head: [['ETAT INTERIEUR']],
            headStyles: {
                fillColor: [255, 0, 0],
                textColor: [255, 255, 255],
                fontSize: 9,
                cellPadding: 0.5
            },
        });

        doc.setFontSize(9);
        doc.text("Sièges Console centrale tableau de bord: Ciel de toit: Garnitures intérieures:", 10, 123);
        doc.text("Revêtement de sol: Coffre: Portes", 10, 127);
        // doc.text("Légende intérieur: S sale  - D déchiré - T troué - E endommagé - M manque - A autre dommage – RAS Rien à signaler", 10, 131);

        // mix normal and bold
        doc.autoTable({
            startY: 128,
            margin: {
                left: 10
            },
            theme: 'plain',
            body: [[{ content: 'Légende intérieur:', styles: { fontStyle: 'bold' } }, { content: 'S', styles: { fontStyle: 'bold' } }, 'Sale -', { content: 'D', styles: { fontStyle: 'bold' } }, 'Déchiré -', { content: 'T', styles: { fontStyle: 'bold' } }, 'Troué -', { content: 'E', styles: { fontStyle: 'bold' } }, 'endommagé -', { content: 'M', styles: { fontStyle: 'bold' } }, 'manque -', { content: 'A', styles: { fontStyle: 'bold' } }, 'autre dommage -', { content: 'RAS', styles: { fontStyle: 'bold' } }, 'Rien à signaler']],
            bodyStyles: {
                fontSize: 8,
                cellPadding: 0.5
            }
        });

        // {content : '' , styles : {fontStyle : 'bold'}}

        // **************** etat extérieur****************
        doc.autoTable({
            startY: 133,
            margin: {
                left: 10
            },
            tableWidth: 192,
            head: [['ETAT EXTERIEUR']],
            headStyles: {
                fillColor: [255, 0, 0],
                textColor: [255, 255, 255],
                fontSize: 9,
                cellPadding: 0.5
            },
        });

        // **************draw 3 rectangles ->milieu contenant image voiture *****************

        //grand rectangle gauche
        doc.setDrawColor(0);
        doc.rect(10, 139, 47, 45, 'D'); // filled red square with black borders

        //rectangle de gauche contenant texte
        doc.setDrawColor(0);
        doc.rect(10, 139, 47, 5, 'D'); // filled red square with black borders

        //texte gauche
        doc.text('Observations au départ', 12, 142);


        //rctangle contenant image voiture
        doc.setDrawColor(0);
        doc.rect(57, 139, 100.5, 45, 'D');
        doc.addImage(imgData, 'JPEG', 65, 145, 100, 50);


        //grand rectangle droite
        doc.setDrawColor(0);
        doc.rect(157.5, 139, 44.5, 45, 'D'); // filled red square with black borders

        //rectangle de droite contenant texte
        doc.setDrawColor(0);
        doc.rect(157.5, 139, 44.5, 5, 'D'); // filled red square with black borders

        //texte de troite
        doc.text("Observations à l'arrivée ", 158, 142);


        //  ******************extension tableau  tableau avec photo voiture******************
        doc.autoTable({
            theme: 'plain',
            startY: 184,
            margin: {
                left: 10
            },
            tableWidth: 192,
            body: [
                [

                    { content: "Carburant au départ", "colSpan": 4, styles: {} },
                    { content: 'Pneus/Jantes ' },
                    { content: ' AV gauche ' },
                    { content: ' AV droite    ' },
                    { content: 'AR gauche ' },
                    { content: 'AR droite  ' },
                    { content: " Carburant au retour", colSpan: 4, styles: {} }

                ],

                [

                    { content: "1/4", rowSpan: 2, styles: { minCellWidth: 10, halign: 'center', valign: 'middle' } },
                    { content: "1/2", rowSpan: 2, styles: { minCellWidth: 11, halign: 'center', valign: 'middle' } },
                    { content: "3/4", rowSpan: 2, styles: { minCellWidth: 11, halign: 'center', valign: 'middle' } },
                    { content: "1/1", rowSpan: 2, styles: { minCellWidth: 10.5, halign: 'center', valign: 'middle' } },
                    { content: 'Marque' },
                    { content: '' },
                    { content: '' },
                    { content: '' },
                    { content: '' },
                    { content: "1/4", rowSpan: 2, styles: { minCellWidth: 10, halign: 'center', valign: 'middle' } },
                    { content: "1/2", rowSpan: 2, styles: { minCellWidth: 10, halign: 'center', valign: 'middle' } },
                    { content: "3/4", rowSpan: 2, styles: { minCellWidth: 10, halign: 'center', valign: 'middle' } },
                    { content: "1/1", rowSpan: 2, styles: { minCellWidth: 10, halign: 'center', valign: 'middle' } }

                ],

                [

                    { content: 'Etat' },
                    { content: '' },
                    { content: '' },
                    { content: '' },
                    { content: '' },

                ],
            ],
            bodyStyles: {
                textColor: [0, 0, 0],
                lineWidth: 0.1, lineColor: [0, 0, 0],
                fillColor: [255, 255, 255],
                cellPadding: 0.5,
                fontSize: 9
            },

        });


        doc.setFontSize(8);
        // doc.text("Légende extérieur: P projection de pierres - R rouille- A autre dommage - R rayure - B bosse - C creux - D dégât accident ", 10, 201);
        // mix normal and bold
        doc.autoTable({
            startY: 198,
            margin: {
                left: 10
            },
            theme: 'plain',
            body: [[
                { content: 'Légende extérieur:', styles: { fontStyle: 'bold' } },
                { content: 'P', styles: { fontStyle: 'bold' } }, 'projection de pierres -',
                { content: 'R', styles: { fontStyle: 'bold' } }, 'rouille -',
                { content: 'A', styles: { fontStyle: 'bold' } }, 'autre dommage -',
                { content: 'R', styles: { fontStyle: 'bold' } }, 'rayure -',
                { content: 'C', styles: { fontStyle: 'bold' } }, 'creux -',
                { content: 'D', styles: { fontStyle: 'bold' } }, 'dégât accident -',
            ]],
            bodyStyles: {
                fontSize: 8,
                cellPadding: 0.5
            }
        });


        //******************prestations*******************

        doc.autoTable({
            theme: 'plain',
            startY: 203,
            margin: {
                left: 8
            },
            tableWidth: 73,
            head: [[{ content: 'PRESTATIONS', colSpan: 2, }]],
            body: [
                [{ content: 'location (31/01-12/02) et (31/01-12/02)', styles: { cellWidth: 36 } }, { content: '123456', styles: { minCellWidth: 37 } }],
                ['lavage (35.5€)', { content: '', styles: { minCellWidth: 37 } }],
                ['Multi-conducteurs (50,00€)', { content: '', styles: { minCellWidth: 37 } }],
                ['Refuelling (80.00€)', { content: '', styles: { minCellWidth: 37 } }],
                ['Siège auto (30.00€)', { content: '', styles: { minCellWidth: 37 } }],
                ['', { content: '', styles: { minCellWidth: 37 } }],
                [{ content: 'Total TTC', styles: { halign: 'right', fontStyle: 'bold' } }, { content: '', styles: { minCellWidth: 37 } }],
                [{ content: 'ACOMPTE', styles: { halign: 'right', fontStyle: 'bold' } }, { content: '', styles: { minCellWidth: 37 } }],
                [{ content: 'RESTE A PAYER', styles: { halign: 'right', fontSize: 7 } }, { content: '', styles: { minCellWidth: 37, fontSize: 7 } }],
                [{ content: 'CAUTION', styles: { halign: 'right', fontSize: 7 } }, { content: '700€', styles: { minCellWidth: 37, fontSize: 7 } }],

            ],
            bodyStyles: {
                textColor: [0, 0, 0],
                lineWidth: 0.1,
                lineColor: [0, 0, 0],
                fillColor: [255, 255, 255],
                cellPadding: 0.5,
                fontSize: 9
            },
            headStyles: {
                fillColor: [255, 0, 0],
                textColor: [255, 255, 255],
                lineWidth: 0.1,
                lineColor: [0, 0, 0],
                halign: 'center',
                cellPadding: 0.5,
                fontSize: 9
            },


        });


        //******************recu de depot caution et franchise*******************

        doc.autoTable({
            theme: 'plain',
            startY: 203,
            margin: {
                left: 81
            },
            tableWidth: 122,
            head: [[{ content: 'RECU DE DEPOT CAUTION ET FRANCHISE' }]],
            body: [
                ['Je sousssigné(e)______ ' + nomclientValue + ' ' + prenomClientValue + '____________________autorise la\nsociété joellocation a prélévé sur mon compte à l\'aide de l\'empreinte de ma carte\nbancaire, le montant de la franchise ou caution du présent contrat ceci en cas d\'accident responsable, de vol, tentative de vol ou dommage occasionné au véhicule loué.'],


            ],
            bodyStyles: {
                textColor: [0, 0, 0],
                lineWidth: 0.1, lineColor: [0, 0, 0],
                fillColor: [255, 255, 255],
                cellPadding: 1,
                fontSize: 9
            },
            headStyles: {
                fillColor: [255, 0, 0],
                textColor: [255, 255, 255],
                lineColor: [0, 0, 0],
                lineWidth: 0.1,
                halign: 'center',
                cellPadding: 0.5,
                fontSize: 9
            },

        });


        //******************tableau en dessous*******************

        doc.autoTable({
            theme: 'plain',
            startY: 230,
            margin: {
                left: 83
            },
            tableWidth: 120,
            body: [

                [

                    { content: 'Date Départ', styes: { cellWidth: 26 } },
                    { content: '26 Mars 2021', styles: { cellWidth: 35 } },
                    { content: 'Date Retour', styles: { styes: { cellWidth: 27, } } },
                    { content: ' ', styles: { cellWidth: 32 } }

                ],
                [

                    { content: 'JOELLOCATION', styles: { fontStyle: 'bold', cellPadding: 2.5 } },
                    { content: '', styles: {} },
                    { content: 'JOELLOCATION', styles: { fontStyle: 'bold', halign: 'center', valign: 'middle' } },
                    { content: '', styles: {} }

                ],

                [

                    { content: 'Locataire\n(lu et\n approuvé)', styles: {} },
                    { content: '', styles: {} },
                    { content: 'Locataire', styes: { styes: {} } },
                    { content: ' ', styles: {} }

                ],

            ],
            bodyStyles: {
                textColor: [0, 0, 0],
                lineWidth: 0.1, lineColor: [0, 0, 0],
                fillColor: [255, 255, 255],
                cellPadding: 0.7,
                fontSize: 8
            },

        });

        //******************case à cocher*******************

        doc.text('Espèce', 8, 263);
        doc.rect(19, 260, 3, 3);

        doc.text('CB', 24, 263);
        doc.rect(29, 260, 3, 3);

        doc.text('Chèque Vacances', 34, 263);
        doc.rect(59, 260, 3, 3);

        doc.text('Chèque', 66, 263);
        doc.rect(78, 260, 3, 3);

        //******************Aucune location*******************
        doc.setFontSize(7)
        doc.text('Aucune location est remboursable. Létat des lieux du véhicule est\nréalisé sur véhicule propre intérieur et extérieur', 8, 268);



        //******************le locataire .. *******************
        doc.setFontSize(7)
        doc.text('Le locataire déclare adhérer à toutes les conditions générales de location', 87, 258);

        doc.setFontSize(9);
        doc.setFillColor(255, 0, 0);
        doc.rect(0, 280, 230, 20, 'F');
        doc.setTextColor(255, 255, 255);
        doc.text('SIRET : 87868990000016 APE: 7711a\n           N°TVA FR40878689900\n              www.joellocation.com', 80, 285);

        //save the pdf file
        doc.save("Contrat" + "_" + nomclientValue + ".pdf");

    }

    function ajouterConducteur() {
        alert('vous avez appuyé sur le bouton');

    }

    //***********************useful fonctions ******************/
    function getFormatedDate(date) {

        var date = new Date(date)
        var day = date.getDate();
        var month = date.getMonth() + 1;

        var year = date.getFullYear()

        switch (month) {
            case 1:
                month = 'Janvier';
                break;
            case 2:
                month = 'Fevrier';
                break;

            case 3:
                month = 'Mars';
                break;
            case 4:
                month = 'Avril';
                break;
            case 5:
                month = 'Mai';
                break;
            case 6:
                month = 'Juin';
                break;
            case 7:
                month = 'Juillet';
                break;
            case 8:
                month = 'Août';
                break;
            case 9:
                month = 'Septembre';
                break;
            case 10:
                month = 'Octobre';
                break;
            case 11:
                month = 'Novembre';
                break;
            case 12:
                month = 'Décembre';
                break;
        }

        if (day < 10) {
            return "0" + day + " " + month + " " + year;
        } else {
            return day + " " + month + " " + year;
        }



    }
});