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

  results.forEach((item) => {
    const resultsId = resultsId.replace("#", "");

    const listItem = jQuery("<li></li>");

    const radioButton = jQuery('<input type="radio" />')
      .attr("name", resultsId)
      .attr("value", item.profile_id)
      .attr("id", "profile_" + item.profile_id);

    const label = jQuery("<label></label>")
      .attr("for", resultsId + "_" + item.profile_id)
      .text(
        item.login +
          " (Orders: <a href='/admin/?target=profile&profile_id=" +
          item.profile_id +
          "'>" +
          item.orders_count +
          "</a>)"
      );

    listItem.append(radioButton).append(label);

    jQuery(resultsId).append(listItem);
  });
};

xcart.autoload(SearchProfile);
