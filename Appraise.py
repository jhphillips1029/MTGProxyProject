from bs4 import *
import urllib
import urllib.request
import requests
import sys
import argparse
import Utilities

deck = "SigardaDeck.txt"

TCG_URL = "https://shop.tcgplayer.com/magic/"
indices = {"Market":2,"Buylist":5,"Median":8}

debug = False
if 'debug' in sys.argv:
	debug = True

def getPrice(cardName,setName,priceType="Market",foil=False,site="TCG"):
	if site=="TCG":
		url = TCG_URL+setName.replace(" ","-")+"/"+cardName.replace(" ","-").replace("'","")
	
	result = requests.get(url)
	html = result.content
	soup = BeautifulSoup(html,"lxml")
	
	trs = soup.find_all('tr')
	price = float(trs[indices[priceType]+foil].find_all('td')[0].getText()[1:])
	
	return price
def log(f,msg):
	print(msg)
	f.write(msg+'\n')
def noArgs():
	f = open(deck,'r')
	decklist = f.readlines()
	f.close

	deckPrice = 0
	maxCardPrice = 0
	maxCardIndex = -1
	minCardPrice = 1000000000
	minCardIndex = -1

	f = open('deckStats.txt','w')

	for i,card in enumerate(decklist):
		card = card.strip()
		foil = False
		if debug:
			print(card)
		cardInfo = card.split('|')
		foil = ( len(cardInfo) > 1 )
		card = cardInfo[0]
		cardName = card.split('   ')[0]
		cardCode = card.split('   ')[1]
		setName = Utilities.expansions[cardCode]
		price = 0
		try:
			price = getPrice(cardName,setName,foil=foil)
		except:
			price = 0
			log(f,'    Error getting price for '+cardName+' ('+cardCode+')')
		deckPrice += price
		if price > maxCardPrice:
			maxCardPrice = price
			maxCardIndex = i
		if price < minCardPrice:
			minCardPrice = price
			minCardIndex = i
		f.write("{} ({}): $ {}\n".format(cardName,card.split('   ')[1],price))

	print('\n')
	log(f,"Deck Appraisal:")
	log(f,"===========================================")
	log(f,"Deck: ${}\n".format(round(deckPrice,2)))
	log(f,"Max:  {}".format(decklist[maxCardIndex]).strip())
	log(f,"      ${}".format(maxCardPrice))
	log(f,"Min:  {}".format(decklist[minCardIndex]).strip())
	log(f,"      ${}".format(minCardPrice))
	log(f,"Avg:  ${}".format(round(deckPrice/len(decklist),2)))

def oneArg(deckName):
	global deck
	deck = deckName
	noArgs()

def twoArgs(card):
	card = card.strip()
	foil = False
	if debug:
		print(card)
	cardInfo = card.split('|')
	foil = ( len(cardInfo) > 1 )
	card = cardInfo[0]
	cardName = card.split('   ')[0]
	setName = Utilities.expansions[card.split('   ')[1]]
	price = 0
	try:
		price = getPrice(cardName,setName,foil=foil)
	except:
		price = 0
	cardCodeFoilInfo = card.split('   ')[1]
	if foil:
		cardCodeFoilInfo = cardCodeFoilInfo+', foil'

	print("{} ({}): $ {}\n".format(cardName,cardCodeFoilInfo,price))

print(sys.argv)
print('\n')

if ( len(sys.argv) == 1 ) or ( len(sys.argv) == 2 and 'debug' in sys.argv ):
	noArgs()
elif ( len(sys.argv) == 2 and not('debug' in sys.argv) ) or ( len(sys.argv) == 3 and 'debug' in sys.argv ):
	oneArg(sys.argv[1])
elif ( len(sys.argv) == 3 and not('debug' in sys.argv) ) or ( len(sys.argv) == 4 and 'debug' in sys.argv ):
	twoArgs(sys.argv[1]+'   '+sys.argv[2])
