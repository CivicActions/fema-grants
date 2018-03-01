Feature: SF-425 Working Prototype

  @api
  Scenario:
  As a Grant Recipient user,
  I want to complete and submit the semi-annual SF-425 financial report,
  so that I can comply with the reporting requirements for my grant award..
    Given I am logged in as a user with the "grantee" role
    And I am on "node/add/sf425"
    Then I should get a "200" HTTP response
