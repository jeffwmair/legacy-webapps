# Legacy Apps

This is a pile of web code (mostly php) that I wrote a few years ago.  It's all garbage, really, but fun to keep around to look at.  I started looking at reviving it with a vagrant script, but left it alone since I can't find a backup of the database I was using (JwmSchema.sql).  Without some real data, it's not much fun to play with.

To run in vagrant environment:
```shell
vagrant up
```

Then connect to http://localhost:8080/WebApps/

Some of the apps and descriptions are:

## Internet Data Usage Tracker

I wrote some php scripts that logged into my account with my ISP and scraped internet usage data so I could share with roommates.

![](/docs/internetusage.png)

## Temperature Tracker

I hooked up a temperature sensor to an arduino uno, wrote some C code for the Uno which pushed the current temperature every few minutes to my database, then served up in a webpage.

![](/docs/temperature.png)

## Korean Study

This is basically a database of Korean phrases for studying the language.

![](/docs/korean.png)

## Ryerson Blackboard Scraper

Scrapes news updates from Ryerson's internal "blackboard" system that students login to for updates for their classes.

![](/docs/ryerson-blackboard.png)

## Crop Price Scraper

I wrote another web scraper for getting crop prices which I served up on a webpage.  For my farming parents.  Not sure they actually used it.

## Apartment Tracker

Another scraper which grabbed data from an apartments listing website.  I think it allows you to mark favorites and write comments.  I forget.

## IPad Internet Usage

Yet another internet usage scraper.  This time for my Mom's ipad.

## etc....

A bunch of other similar things.
