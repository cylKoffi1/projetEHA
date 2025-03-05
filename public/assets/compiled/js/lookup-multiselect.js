class LookupMultiSelect extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: "open" });

        this.shadowRoot.innerHTML = `
        <style>
            .custom-dropdown {
                position: relative;
                width: 100%;
            }
            .dropdown-list {
                width: auto;
                background: white;
                border: 1px solid #ccc;
                border-radius: 5px;
                max-height: 200px;
                overflow-y: auto;
                display: none;
                box-shadow: 0px 4px 6px rgba(0,0,0,0.1);
            }
            .dropdown-item {
                display: flex;
                align-items: center;
                padding: 10px;
                cursor: pointer;
                color: #333333;
                font-size: 12.8px;
                background: white;
            }
            .dropdown-item:hover, .dropdown-item.selected {
                background: #435ebe;
                color: white;
            }
            .dropdown-item input {
                margin-right: 10px;
            }
            .selected-items {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                border: 1px solid #ccc;
                border-radius: 5px;
                padding: 5px;
                min-height: 23px;
                cursor: text;
                background: white;
                color: #333333;
                position: relative;
            }
            .form-control {
                border: none;
                outline: none;
                width: 100%;
                padding: 5px;
            }
            .select-all {
                display: flex;
                align-items: center;
                padding: 10px;
                color: #333333;
                border-bottom: 1px solid #ccc;
                font-weight: bold;
                cursor: pointer;
            }
            #select-all {
                margin-right: 10px;
                color: #333333;
            }
            .table-custom {
                    width: 100%;
                    border-collapse: collapse;
                    background: white;
                }
                .table-custom tr {
                    border-bottom: 1px solid #ddd;
                    background:rgb(183, 180, 209);

                }
                .table-custom td {
                    padding: 10px;
                    text-align: left;
                    color: #333;
                    font-size: 13px;
                }
                .table-custom span {
                    color: red;
                    font-size: 16px;
                    cursor: pointer;
                    font-weight: bold;
                }
                .table-custom span:hover {
                    color: darkred;
                }
        </style>
        <div class="custom-dropdown">
            <div class="selected-items" tabindex="0">
                <input type="text" class="form-control" style="color: black;" placeholder="Sélectionner des options..." autocomplete="off">
            </div>
            <div class="dropdown-list">
                <div class="select-all">
                    <input type="checkbox" id="select-all" style="color: black;"> Tout sélectionner
                </div>
            </div>
        </div>
        `;

    }
    connectedCallback() {
        this.input = this.shadowRoot.querySelector(".form-control");
        this.dropdownList = this.shadowRoot.querySelector(".dropdown-list");
        this.selectedContainer = this.shadowRoot.querySelector(".selected-items");
        this.selectAllCheckbox = this.shadowRoot.querySelector("#select-all");
        this.selectedOptions = [];

        this.options = Array.from(this.querySelectorAll("option")).map(option => ({
            value: option.value,
            text: option.textContent
        }));

        this.populateDropdown();
        this.addEventListeners();
    }

    populateDropdown() {
        this.options.forEach(option => {
            let div = document.createElement("div");
            div.className = "dropdown-item";

            let checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.dataset.value = option.value;
            checkbox.onchange = () => this.toggleOption(option, checkbox.checked);

            div.appendChild(checkbox);
            div.appendChild(document.createTextNode(option.text));
            this.dropdownList.appendChild(div);
        });
    }

    addEventListeners() {
        this.selectedContainer.addEventListener("click", () => {
            this.input.focus();
            this.showDropdown();
        });

        document.addEventListener("click", (event) => {
            if (!this.contains(event.target)) {
                this.hideDropdown();
            }
        });

        this.selectAllCheckbox.addEventListener("change", (event) => {
            let checked = event.target.checked;
            this.toggleAll(checked);
        });
    }

    toggleOption(option, isChecked) {
        let item = this.dropdownList.querySelector(`.dropdown-item input[data-value='${option.value}']`).parentElement;
        if (isChecked) {
            if (!this.selectedOptions.find(opt => opt.value === option.value)) {
                this.selectedOptions.push(option);
            }
            item.classList.add("selected");
        } else {
            this.selectedOptions = this.selectedOptions.filter(opt => opt.value !== option.value);
            item.classList.remove("selected");
        }
        this.updateSelectedDisplay();
        this.dispatchChangeEvent();
    }

    toggleAll(isChecked) {
        this.selectedOptions = isChecked ? [...this.options] : [];
        this.dropdownList.querySelectorAll(".dropdown-item input").forEach(checkbox => {
            checkbox.checked = isChecked;
            let item = checkbox.parentElement;
            if (isChecked) {
                item.classList.add("selected");
            } else {
                item.classList.remove("selected");
            }
        });
        this.updateSelectedDisplay();
        this.dispatchChangeEvent();
    }

    updateSelectedDisplay() {
        this.selectedContainer.innerHTML = "";

        let table = document.createElement("table");
        table.className = "table-custom";

        let tbody = document.createElement("tbody");

        this.selectedOptions.forEach(option => {
            let row = document.createElement("tr");

            let textCell = document.createElement("td");
            textCell.className = "col-10";
            textCell.textContent = option.text;

            let actionCell = document.createElement("td");
            actionCell.className = "col-2";

            let closeBtn = document.createElement("span");
            closeBtn.textContent = "❌";
            closeBtn.style.cursor = "pointer";
            closeBtn.onclick = () => this.removeOption(option);

            actionCell.appendChild(closeBtn);

            row.appendChild(textCell);
            row.appendChild(actionCell);
            tbody.appendChild(row);
        });

        table.appendChild(tbody);
        this.selectedContainer.appendChild(table);
        this.selectedContainer.appendChild(this.input);
        this.input.value = "";

        // Déclencher l'événement `change` pour détecter la mise à jour de la sélection
        this.dispatchChangeEvent();
    }


    removeOption(option) {
        this.selectedOptions = this.selectedOptions.filter(opt => opt.value !== option.value);
        let checkbox = this.dropdownList.querySelector(`.dropdown-item input[data-value='${option.value}']`);
        if (checkbox) {
            checkbox.checked = false;
            checkbox.parentElement.classList.remove("selected");
        }
        this.updateSelectedDisplay();
        this.dispatchChangeEvent();
    }

    showDropdown() {
        this.dropdownList.style.display = "block";
    }

    hideDropdown() {
        this.dropdownList.style.display = "none";
    }

    get value() {
        return this.selectedOptions.map(opt => opt.value);
    }

    set value(newValues) {
        this.selectedOptions = this.options.filter(opt => newValues.includes(opt.value));
        this.updateSelectedDisplay();
    }

    dispatchChangeEvent() {
        this.dispatchEvent(new Event("change"));
    }
}

customElements.define("lookup-multiselect", LookupMultiSelect);
