function SearchProfile(base) {
  SearchProfile.superclass.constructor.apply(this, arguments);

  this.bind("local.loaded", _.bind(this.setHandler, this));
  this.setHandler("#search-1", "#results-1");
  this.setHandler("#search-2", "#results-2");
}

extend(SearchProfile, ALoadable);

SearchProfile.autoload = function () {
  jQuery("#merge-profiles").each(function () {
    new SearchProfile(this);
  });
};

SearchProfile.prototype.setHandler = function (searchId, resultsId) {
  jQuery(searchId).on("input", async (e) => {
    const query = e.target.value;
    if (query.length === 0 && jQuery(resultsId).length !== 0) {
      jQuery(resultsId).empty();
    }

    if (query.length < 3) return;
    jQuery(resultsId).text("Loading...");
    let results = await this.doSearch(query);
    this.showSearchResults(resultsId, results);
  });
};

SearchProfile.prototype.doSearch = async function (query) {
  let result = [];

  const params = {
    target: "merge_profiles",
    action: "search_profiles",
    query: query,
  };
  const url = URLHandler.buildURL(params);

  await xcart.get(url, function (response) {
    result = JSON.parse(response.responseText);
  });

  return result;
};

SearchProfile.prototype.showSearchResults = function (resultsId, results) {
  jQuery(resultsId).empty();

  function showOrders(item, listItem, id) {
    const listTitle = jQuery("<p></p>").text("Orders: " + item.orders_count);

    const listBtn = jQuery("<button></button>")
      .attr("class", "btn-show list-btn")
      .attr("type", "button")
      .text("show");

    const selectAllBtn = jQuery("<button></button>")
      .attr("class", "btn-select list-btn")
      .attr("type", "button")
      .text("select all");

    const ordersList = jQuery("<ul></ul>").attr("class", "hidden");

    listBtn.on("click", function () {
      ordersList.toggleClass("hidden");

      if (ordersList.hasClass("hidden")) {
        jQuery(this).text("show");
      } else {
        jQuery(this).text("hide");
      }
    });

    selectAllBtn.on("click", function () {
      ordersList.find('input[type="checkbox"]').prop("checked", true);
    });

    item.orders.forEach((order) => {
      const [orderId, orderNumber] = Object.entries(order)[0];

      const orderListItem = jQuery("<li></li>");
      const orderCheckbox = jQuery('<input type="checkbox" />')
        .attr("name", "orders_" + item.profile_id+"[]")
        .attr("value", orderId)
        .attr("id", "order_" + orderId);

      const orderLabel = jQuery("<label></label>")
        .attr("for", "order_" + orderId)
        .text("#" + orderNumber);

      orderListItem.append(orderCheckbox).append(orderLabel);
      ordersList.append(orderListItem);
    });
    if (item.orders.length && id === "results-1") {
      listTitle.append(listBtn);
      listTitle.append(selectAllBtn);
    }

    listItem.append(listTitle);

    if (id === "results-1") {
      listItem.append(ordersList);
    }
  }

  results.forEach((item) => {
    const id = resultsId.replace("#", "");

    const listItem = jQuery("<li></li>").attr("id", item.profile_id);

    const inputId = id + "_" + item.profile_id;

    const radioButton = jQuery('<input type="radio" />')
      .attr("name", id)
      .attr("required", true)
      .attr("value", item.profile_id)
      .attr("id", inputId);

    const label = jQuery("<label></label>")
      .attr("id", "label_" + inputId)
      .attr("for", inputId);

    const link = jQuery("<a></a>")
      .attr("href", "/admin/?target=profile&profile_id=" + item.profile_id)
      .attr("target", "_blank")
      .text(item.login);

    label.prepend(link);

    listItem.append(radioButton).append(label);

    showOrders(item, listItem, id);

    jQuery(resultsId).append(listItem);
  });
};

xcart.autoload(SearchProfile);
