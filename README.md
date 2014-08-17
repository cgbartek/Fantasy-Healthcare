Fantasy Healthcare
==================

General Overview
----------------
Fantasy Healthcare is a loose adaptation of the fantasy football game concept, whereas players build their own healthcare providers and compete against others using real community data.

Features
--------
- Draft team of "players" and compete against 5 others
- Uses real ratings data to calculate winners
- Players have fun while learning about how providers compete in certain areas
- 100% pure HTML5 front end (runs on all modern platforms)
- Extensible back end for easy expansion of additional AF4Q data

Game Elements
-------------
- Incidental (Login, Signup, Menu, etc.)
- Join / Create Game
- League Dashboard
- League Draft
- Game Play
- Minigames (Trivia, Survey, etc.)
- Results

System overview
---------------
- HTML5 front end
- PHP back end
- MySQL relational database

Data Usage and Generation
-------------------------
- Uses AF4Q Wisconsin data for player stats
- Collects survey data for AF4Q
- Generates trivia answer data
- Generates game results (who users pick, results, etc.)
- Accessible via API as JSON data

Changes from Phase I
--------------------
- Generally simplified (complexity led to confusion)
- Team capacity from 8 to 6 (speed up game)
- County-based factors removed (overcomplicated game)
- No revealing of provider overall ranking (more fun that way)

Changes based on Mentor Feedback
--------------------------------
- Added general survey and award game points for completion
- Re-tweaked calculations for speed and purpose instead of fantasy football accuracy
- Subtractive comparisons instead of winner-take-all

Goals
-----
- Introduce players to healthcare provider options in their area
- Incentivize players to research providers independently
- Allow friendly competition in a fun, easy-to-play, cross-platform casual game
- Capture a wide variety of audiences including sports fans, children, adults, and seniors.

Data Created
------------
- Survey and trivia results collected from players, incentivized through additional point bonuses.

Data Utilized 
-------------
- Aligning Forces for Quality data (for statistics).

Motivation
----------
- Public health industry inspiration
- Thought idea was novel enough to see through to completion
- Personal enjoyment of video game development as a hobby
- Love a good challenge!

Future
------
- More minigames
- Add more AF4Q regions (Minnesota, etc.)
- More team views available to users
- In-game chat rooms to taunt other players
- Finish native ports to Android and iOS platforms
- Looking at additional target platforms (XBLA, Ouya, WP8, FB)
- Tighter, more robust Facebook and Twitter integration
- Utilizing additional Aligning Forces data and adding to game
- Tweaking rules to be more or less fantasy football-esque
- Alternative user interface for landscape-friendly displays
