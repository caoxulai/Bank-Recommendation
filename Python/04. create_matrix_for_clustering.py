import scipy

__author__ = 'nathan'
# from scipy.cluster.vq import kmeans, kmeans2, whiten
from scipy.cluster.vq import kmeans, kmeans2, whiten
import cPickle as pickle
import numpy
import timeit

# Initiate tiemr
start = timeit.default_timer()

def create_MN_matrix(n, data_file_name):
    """
    svm_read_problem(data_file_name) -> [y, x]

    Read LIBSVM-format data from data_file_name and return labels y
    and data instances x.
    """
    prob_y = []
    prob_x = []
    # [0 for x in range(n)] for x in range(m)
    for line in open(data_file_name):
        line = line.split(None, 1)
        # In case an instance with all zero features
        if len(line) == 1: line += ['']
        label, features = line
        xi = [0 for x in range(n)]
        for e in features.split():
            ind, val = e.split(":")
            xi[int(ind)] = float(val)
        prob_y += [float(label)]
        prob_x += [xi]
    return prob_y, prob_x


with open('dictionary.pkl', 'rb') as input:
    dic_file = pickle.load(input)
N = dic_file.tot_word
print(N)

y, x = create_MN_matrix( N, 'raw_label_feature')

a = numpy.asarray(x)
numpy.savetxt("feature.csv", a, delimiter=",")

b = numpy.asarray(y)
numpy.savetxt("label.csv", b, delimiter=",")


# print(x)

# kmeans for 3 clusters
# res, idx = kmeans2(x, 8)
#
# df = DataFrame(index = rows, columns = cols )
# df = df.fillna(0)
#
# #assign all nonzero values in dataframe
# for key, value in doc_term_dict.items():
#     df[key[1]][key[0]] = value

#Your statements here
stop = timeit.default_timer()

print "Time: " + "{0:.2f}".format(stop - start) + "s"
