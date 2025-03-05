class LookupSelect extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: "open" });

        // Création des styles
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
                    padding: 10px;
                    cursor: pointer;
                    color: #333333;
                    font-size: 12.8px;
                }
                .dropdown-item:hover {
                    background: #435ebe;
                    color: white;
                }
                .form-control {
                    display: block;
                    width: 100%;
                    padding: 8px;
                    border: 1px solid #ccc;
                    border-radius: 5px;
                    outline: none;
                    background: white;
                }
            </style>
            <div class="custom-dropdown">
                <input type="text" class="form-control" placeholder="Sélectionner une option..." autocomplete="off">
                <div class="dropdown-list"></div>
            </div>
        `;
    }

    connectedCallback() {
        this.name = this.getAttribute("name") || "";
        this.id = this.getAttribute("id") || "";
        this.input = this.shadowRoot.querySelector("input");
        this.dropdownList = this.shadowRoot.querySelector(".dropdown-list");

        // Récupération des options internes
        this.options = Array.from(this.querySelectorAll("option")).map(option => ({
            value: option.value,
            text: option.textContent
        }));

        this.populateDropdown();
        this.addEventListeners();
    }

    populateDropdown() {
        this.dropdownList.innerHTML = "";
        this.options.forEach(option => {
            let div = document.createElement("div");
            div.className = "dropdown-item";
            div.textContent = option.text;
            div.dataset.value = option.value;
            div.onclick = () => this.selectOption(option);
            this.dropdownList.appendChild(div);
        });
    }

    addEventListeners() {
        this.input.addEventListener("focus", () => {
            this.showDropdown();
        });

        this.input.addEventListener("input", () => {
            let filter = this.input.value.toLowerCase();
            Array.from(this.dropdownList.children).forEach(item => {
                item.style.display = item.textContent.toLowerCase().includes(filter) ? "block" : "none";
            });
            this.showDropdown();
        });

        document.addEventListener("click", (event) => {
            if (!this.contains(event.target) && !this.shadowRoot.contains(event.target)) {
                this.hideDropdown();
            }
        });
    }

    selectOption(option) {
        this.input.value = option.text;
        this.setAttribute("value", option.value);
        this.dispatchChangeEvent();
        this.hideDropdown();
    }

    showDropdown() {
        this.dropdownList.style.display = "block";
    }

    hideDropdown() {
        this.dropdownList.style.display = "none";
    }

    get value() {
        return this.getAttribute("value");
    }

    set value(newValue) {
        let option = this.options.find(opt => opt.value === newValue);
        if (option) {
            this.selectOption(option);
        }
    }

    dispatchChangeEvent() {
        this.dispatchEvent(new Event("change"));
    }
}

customElements.define("lookup-select", LookupSelect);
