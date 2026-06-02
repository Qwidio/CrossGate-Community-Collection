function linker(reqstate) {
    window.location.replace(reqstate);
};

let bannerIndex;
function createBannerElem(libsBanners, gids, containerId) {
    bannerIndex = 0;
    const container = document.getElementById(containerId);
    container.innerHTML = "";
    const bannerList = JSON.parse(libsBanners);
    bannerList.forEach(libsBanner => {
        bannerIndex++;
        const bannerId = `bannerPrev${bannerIndex}`;
        const inputName = `banners${bannerIndex}`;

        const div = document.createElement("div");
        div.id = `bannerCon${bannerIndex}`;
        div.className = "posr r16-9 h50 flex fld acjc gap5";
        const img = document.createElement("img");
        img.id = bannerId;
        img.className = "posr sideMg wh100p coverfit bg-half-gray";
        let imageName = libsBanner;
        if (typeof imageName === 'string') {
            imageName = imageName.replace('["', '').replace('"]', '');
        }
        img.src = `../Library/libsImg/${gids}/${imageName}`;
        const input = document.createElement("input");
        input.type = "file";
        input.name = inputName;
        input.accept = "image/*";
        input.required = false;
        input.className = "posa c0 wh100p txtc";
        input.onchange = function(event) {
            uniLoadFile(event, bannerId);
        };
        input.addEventListener("change", (event) => { uniLoadFile(event, bannerId); });
        div.appendChild(img);
        div.appendChild(input);
        container.appendChild(div);
    });
    if (bannerIndex == 0) {
        bannerIndex++;
        const bannerId = `bannerPrev${bannerIndex}`;
        const inputName = `banners${bannerIndex}`;
        const div = document.createElement("div");
        div.id = `bannerCon${bannerIndex}`;
        div.className = "posr r16-9 h50 flex fld acjc gap5";
        const img = document.createElement("img");
        img.id = bannerId;
        img.className = "posr sideMg wh100p coverfit bg-half-gray";
        const input = document.createElement("input");
        input.type = "file";
        input.name = inputName;
        input.accept = "image/*";
        input.required = false;
        input.className = "posa c0 wh100p txtc";
        input.onchange = function(event) {
            uniLoadFile(event, bannerId);
        };
        input.addEventListener("change", (event) => { uniLoadFile(event, bannerId); });
        div.appendChild(img);
        div.appendChild(input);
        container.appendChild(div);
    }
    if (bannerIndex < 11) {
        const p = document.createElement("p");
        p.id = "add_btn2";
        p.className = "posr pad-n-s h100p flex acjc txt-30 bg-half-gray hover-white points";
        p.onclick = function() { newElemt('bannerContainer2','img','clts','add_btn2'); };
        p.innerHTML = "+";
        container.appendChild(p);
    }
}
let linkIndex;
function createLinkElem(extlink, containerId) {
    linkIndex = 0;
    const container = document.getElementById(containerId);
    container.innerHTML = "";
    const linkList = JSON.parse(extlink);
    Object.entries(linkList).forEach(([name, value]) => {
        linkIndex++;
        const div = document.createElement("div");
        div.id = `linkCon${linkIndex}`;
        div.className = "posr topMg-s5 pad-m w100p flex fld border-1 bora-s";
        const inputName = `linkname${linkIndex}`;
        const linkname = document.createElement("input");
        linkname.type = "text";
        linkname.name = inputName;
        linkname.required = false;
        linkname.className = "inptxt";
        linkname.maxLength = "255";
        linkname.placeholder = `Site name ${linkIndex}`;
        linkname.value = name;
        const inputlinkName = `extlink${linkIndex}`;
        const inputlink = document.createElement("input");
        inputlink.type = "text";
        inputlink.name = inputlinkName;
        inputlink.required = false;
        inputlink.className = "inptxt";
        inputlink.maxLength = "1000";
        inputlink.placeholder = `Link ${linkIndex}`;
        inputlink.value = value;
        div.appendChild(linkname);
        div.appendChild(inputlink);
        container.appendChild(div);
    });
    if (linkIndex == 0) {
        linkIndex++;
        const div = document.createElement("div");
        div.id = `linkCon${linkIndex}`;
        div.className = "posr topMg-s5 pad-m w100p flex fld border-1 bora-s";
        const inputName = `linkname${linkIndex}`;
        const linkname = document.createElement("input");
        linkname.type = "text";
        linkname.name = inputName;
        linkname.required = false;
        linkname.className = "inptxt";
        linkname.maxLength = "255";
        linkname.placeholder = `Site name ${linkIndex}`;
        const inputlinkName = `extlink${linkIndex}`;
        const inputlink = document.createElement("input");
        inputlink.type = "text";
        inputlink.name = inputlinkName;
        inputlink.required = false;
        inputlink.className = "inptxt";
        inputlink.maxLength = "1000";
        inputlink.placeholder = `Link ${linkIndex}`;
        div.appendChild(linkname);
        div.appendChild(inputlink);
        container.appendChild(div);
    }
}

function newElemt(containerIds, type, nameId, btnId) {
    const mainContainer = document.getElementById(containerIds);
    if (type === "img" && nameId == "clts") {
        var add_btn = document.getElementById(btnId);
        add_btn.remove();

        if (bannerIndex == 0) {
            bannerIndex = 1;
        }
        bannerIndex++;
        const bannerId = `bannerPrev${bannerIndex}`;
        const inputName = `banners${bannerIndex}`;
        const div = document.createElement("div");
        div.id = `bannerCon${bannerIndex}`;
        div.className = "posr r16-9 h50 flex fld acjc gap5";
        const img = document.createElement("img");
        img.id = bannerId;
        img.className = "posr sideMg wh100p coverfit bg-half-gray";
        img.src = ``;
        const input = document.createElement("input");
        input.type = "file";
        input.name = inputName;
        input.accept = "image/*";
        input.required = false;
        input.className = "posa c0 wh100p txtc";
        input.addEventListener("change", (event) => { uniLoadFile(event, `bannerPrev${bannerIndex}`); });

        div.appendChild(img);
        div.appendChild(input);
        mainContainer.appendChild(div);
        if (bannerIndex < 11) {
            const p = document.createElement("p");
            p.id = `${btnId}`;
            p.className = "posr pad-n-s h100p flex acjc txt-30 bg-half-gray hover-white points";
            p.onclick = function() { newElemt(`${containerIds}`,'img','clts',`${btnId}`); };
            p.innerHTML = "+";
            mainContainer.appendChild(p);
        }
    } else if (type === "link" && nameId == "extlink") {
        if (linkIndex == 0) {
            linkIndex = 1;
        }
        linkIndex++;
        const div = document.createElement("div");
        div.id = `linkCon${linkIndex}`;
        div.className = "posr topMg-s5 pad-m w100p flex fld border-1 bora-s";
        const inputName = `linkname${linkIndex}`;
        const linkname = document.createElement("input");
        linkname.type = "text";
        linkname.name = inputName;
        linkname.required = false;
        linkname.className = "inptxt";
        linkname.maxLength = "255";
        linkname.placeholder = `Site name ${linkIndex}`;
        const inputlinkName = `extlink${linkIndex}`;
        const inputlink = document.createElement("input");
        inputlink.type = "text";
        inputlink.name = inputlinkName;
        inputlink.required = false;
        inputlink.className = "inptxt";
        inputlink.maxLength = "1000";
        inputlink.placeholder = `Link ${linkIndex}`;
        div.appendChild(linkname);
        div.appendChild(inputlink);
        mainContainer.appendChild(div);
    }
}

function uniDisplaySwitch(Ids) {
    var ChangedElem = document.getElementById(Ids);
    if (ChangedElem) {
        ChangedElem.style.display = (ChangedElem.style.display === 'none' || ChangedElem.style.display === '') ? "flex" : "none";
    }
}

function copy(id) {
  var copyText = document.getElementById(id);
  copyText.select();
  copyText.setSelectionRange(0, 99999);
  navigator.clipboard.writeText(copyText.value);
  alerter("Copied to clipboard");
} 

var uniLoadFile = function(event, ids) {
    var output = document.getElementById(ids);
    const file = event.target.files[0];
    if (!file) return;
    output.src = URL.createObjectURL(file);
    output.onload = function() {
      URL.revokeObjectURL(output.src)
    }
};
function uniReloadFile(imgObj, ids) {
    var output = document.getElementById(ids);
    output.src = imgObj;
    output.onload = function() {
      URL.revokeObjectURL(output.src)
    }
};
function uniLoad(ReqstData, ids) {
    const form = document.getElementById(ids);
    if (!form) {
        console.error(`Form with ID "${ids}" not found.`);
        return;
    }
    const values = ReqstData.dataset;
    Object.keys(values).forEach((key) => {
        const field = form.elements[key];
        if (field) {
            field.value = values[key];
        } else {
            console.warn(`Field "${key}" not found in form "${ids}"`);
        }
    });
};