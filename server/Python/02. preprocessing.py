"""
    The implementation code of pr-processing code is based on https://github.com/ravikiranj/twitter-sentiment-analyzer
"""
import re
import csv
from nltk.stem.lancaster import LancasterStemmer
import pickle
import random
import json
from pprint import pprint

import timeit

# Initiate tiemr
start = timeit.default_timer()

# start replaceTwoOrMore
def replaceTwoOrMore(s):
    pattern = re.compile(r"(.)\1{1,}", re.DOTALL)
    return pattern.sub(r"\1\1", s)


# start process_text
def processText(text):
    #  process the texts

    # Convert to lower case
    text = text.lower()
    # Convert www.* or https?://* to URL
    text = re.sub('((www\.[^\s]+)|(https?://[^\s]+))', ' ', text)
    # Convert @username to AT_USER
    text = re.sub('@[^\s]+', ' ', text)
    # Remove additional white spaces
    text = re.sub('[\s]+', ' ', text)
    # Replace # word with word
    text = re.sub(r'# ([^\s]+)', r'\1', text)
    # remove quot,&amp; &gt; &lt;
    text = re.sub(r'&quot;', '', text)
    text = re.sub(r'&amp', '', text)
    text = re.sub(r'&gt;', '', text)
    text = re.sub(r'&lt;', '', text)
    # remove extra symbols
    text = re.sub('[\']', '', text)
    text = re.sub('[\+\=\.\,\~\{\}\^\;\:\*\)\(\[\]\!\-\n\?]', ' ', text)
    text = re.sub('[\\\/]', ' ', text)

    text = text.replace("\\", " ")

    # trim
    text = text.strip('\'"')
    return text


# end


# start getStopWordList
def getStopWordList(stopWordListFileName):
    # read the stopwords
    stopWords = []
    stopWords.append('AT_USER')
    stopWords.append('URL')

    fp = open(stopWordListFileName, 'r')
    line = fp.readline()
    while line:
        word = line.strip()
        stopWords.append(word)
        line = fp.readline()
    fp.close()
    return stopWords


# end


# start getfeatureVector
def getFeatureVector(text, stopWords):
    featureVector = []
    words = text.split()
    # print(text)
    # words = re.split(r' ',text)
    # print(words)
    for w in words:
        # replace two or more with two occurrences
        w = replaceTwoOrMore(w)
        # strip punctuation
        w = w.strip('\'"?,.!')
        w = w.lower()
        # check if it consists of only words
        # val = re.search(r"^[a-zA-Z][a-zA-Z0-9]*[a-zA-Z]+[a-zA-Z0-9]*$", w)
        val = re.search(r"[a-zA-Z]+$", w)
        val_num = re.search(r"[a-zA-Z]*[0-9]+[a-zA-Z]*$", w)

        if len(w) <= 1:
            continue

        if w in stopWords:
            continue

        if (not val_num is None):
            continue
        # ignore if it is a stopWord
        # if(w in stopWords or val is None):
        if (val is None):
            # print(w)
            continue
        else:
            # print(w,' '),
            featureVector.append(w)
    return featureVector


# # Read the tweets one by one and process it
# # inpTweets = csv.reader(open('data/sampleTweets.csv', 'r'), delimiter=',', quotechar='|')
# Sfilename = 'C:\\Users\\Maxwell\\PycharmProjects\\trainingandtestdata\\training.1600000.processed.noemoticon.csv'
# # Sfilename = 'C:\\Users\\Maxwell\\PycharmProjects\\trainingandtestdata\\testdata.manual.2009.06.14.csv'
# tmp_smp_filename = "f:\\tweet_small_samples.txt"
# featureVectorst = LancasterStemmer()
# st = LancasterStemmer()
# if 1:
#     small_smps_saver(Sfilename,tmp_smp_filename)    # read the source database and save some samples into a tmp file
# with open(tmp_smp_filename, 'rb') as f: # load the tmp file(avoid reading database everytime)
#     inpTweets = pickle.load(f)
# # for i in range(0,len(inpTweets)):
# #     print(i,inpTweets[i])
# print('len(inpTweets)=',len(inpTweets))


with open('merged_data.json') as data_file:
    data = json.load(data_file)
# pprint(data)

st = LancasterStemmer()

stopWords = getStopWordList('StopwordsList.txt')
pprint(stopWords)

count = 0
# featureList = []
processed_data = []
# print(inpTweets)
print('Processing Text')

for node in data:
    processed_n = processText(node['name'])
    featureVector_n = getFeatureVector(processed_n, stopWords)
    featureVector_n = [st.stem(e) for e in featureVector_n]

    processed_d = processText(node['description'])
    featureVector_d = getFeatureVector(processed_d, stopWords)
    featureVector_d = [st.stem(e) for e in featureVector_d]

    new_node = {}
    new_node['ori_label'] = node['ori_label']
    new_node['name'] = node['name']
    new_node['fea_n'] = featureVector_n
    new_node['fea_d'] = featureVector_d

    processed_data.append(new_node)
    # processed_data.append((featureVector_n, featureVector_d, node['name'], node['ori_label']))
# end loop

# # This is a check to see whether preprocessing is successful or not
# for i in range(0, len(processed_data)):
#     print(i, data[i])
#     print(i, "PP ", processed_data[i])
#     print('\n')

# Write to Json file
with open('processed_data.json', 'w') as outfile:
    json.dump(processed_data, outfile)


# tweetFilename = 'test_sample_11250012.pickle'
# with open(tweetFilename, 'wb') as f:
#     pickle.dump(tweets, f)
#
# with open(tweetFilename, 'rb') as f:  # load the tmp file(avoid reading database everytime)
#     tryloadtweet = pickle.load(f)
# print(tryloadtweet)

#Your statements here
stop = timeit.default_timer()

print "Time: " + "{0:.2f}".format(stop - start) + "s"
